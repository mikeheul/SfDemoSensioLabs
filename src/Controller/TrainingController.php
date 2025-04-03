<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use App\Entity\Notification;
use Psr\Log\LoggerInterface;
use App\Service\TrainingService;
use App\Service\NotificationService;
use App\Event\TrainingEnrollmentEvent;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Message\TrainingEnrollmentNotification;
use Symfony\Component\Workflow\WorkflowInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/training')]
final class TrainingController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService, 
        private TrainingService $trainingService, 
        private PaginatorInterface $paginator,
        private EntityManagerInterface $entityManager,
        private TrainingRepository $trainingRepository,
        private SerializerInterface $serializer,
        private EventDispatcherInterface $dispatcher,
        private MessageBusInterface $bus,
        private LoggerInterface $logger,
        private WorkflowInterface $trainingWorkflow,
        private SluggerInterface $slugger)
    {}

    #[Route('/', name: 'app_training')]
    public function index(Request $request, Security $security): Response
    {
        try {

            $user = $security->getUser();
            $trainings = ($user && in_array('ROLE_ADMIN', $user->getRoles())) 
                ? $this->trainingService->getAllTrainings() 
                : $this->trainingService->getAllTrainingsConfirmed();

            $pagination = $this->paginator->paginate(
                $trainings,
                $request->query->getInt('page', 1),
                6
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while fetching trainings.');
            return $this->redirectToRoute('app_home'); 
        }

        return $this->render('training/index.html.twig', [
            'trainings' => $pagination,
        ]);
    }

    #[Route('/getTrainingsAPI', name: 'trainings_api')]
    public function getTrainingsAPI()
    {
        try {
            $trainings = $this->trainingRepository->findBy([], ["title" => "ASC"]);
            $jsonContent = $this->serializer->serialize($trainings, 'json', ['groups' => ['training_detail']]);
            return new JsonResponse($jsonContent, Response::HTTP_OK, [], true);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to fetch trainings.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/new', name: 'new_training')]
    public function new(Request $request): Response
    {
        $form = $this->createForm(TrainingType::class, new Training());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $training = $form->getData();

                $slug = $this->slugger->slug($training->getTitle());
                $training->setSlug($slug);

                $this->entityManager->persist($training);
                $this->entityManager->flush();

                $this->addFlash('success', 'Training successfully created.');
                return $this->redirectToRoute('app_training');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Failed to save the training.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An unexpected error occurred.');
            }
        }
        return $this->render('training/new.html.twig', [
            'formAddTraining' => $form,
        ]);
    }

    #[Route('/{slug}', name: 'show_training')]
    public function show(string $slug): Response
    {
        try {
            $training = $this->trainingService->getTrainingBySlug($slug);

            if (!$training) {
                throw new NotFoundHttpException('Training not found.');
            }

            $user = $this->getUser();
            $isEnrolled = $user && in_array('ROLE_STUDENT', $user->getRoles()) 
                ? $training->getTrainees()->contains($user) 
                : false;

            $coursesNotInTraining = $this->trainingRepository->findCoursesNotInTraining($training);
        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', 'Training not found.');
            return $this->redirectToRoute('app_training');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while fetching the training details.');
            return $this->redirectToRoute('app_training');
        }

        return $this->render('training/show.html.twig', [
            'training' => $training,
            'isEnrolled' => $isEnrolled,
            'coursesNotInTraining' => $coursesNotInTraining,
        ]);
    }

    #[Route('/{id}/enroll', name: 'enroll_training')]
    public function toggleEnrollment(Training $training): Response
    {
        try {
            $user = $this->getUser();

            if (!$user || !in_array('ROLE_STUDENT', $user->getRoles())) {
                throw new \Exception('You do not have permission to enroll in this training.');
            }

            $isEnrolled = !$training->getTrainees()->contains($user);

            if (!$isEnrolled) {
                $training->removeTrainee($user);
                $this->notificationService->createNotification('You have successfully unenrolled from the training: ' . $training->getTitle(), $user);
            } else {
                $training->addTrainee($user);
                $this->notificationService->createNotification('You are now enrolled in this training: ' . $training->getTitle(), $user);
                
                // $this->bus->dispatch(new TrainingEnrollmentNotification(
                //     $user->getEmail(),
                //     $training->getTitle()
                // ));
            }

            $this->entityManager->persist($training);
            $this->entityManager->flush();

            $this->dispatcher->dispatch(
                new TrainingEnrollmentEvent($user, $training, $isEnrolled), 
                $isEnrolled ? TrainingEnrollmentEvent::ENROLLED : TrainingEnrollmentEvent::UNENROLLED
            );

            $this->addFlash('success', 'Your enrollment status has been updated.');

        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to update enrollment status.');
            $this->logger->error('Error: ' . $e->getMessage());
        }

        return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
    }

    #[Route('/training/{id}/to-review', name: 'training_to_review')]
    #[IsGranted("ROLE_ADMIN")]
    public function toReview(Training $training): Response
    {
        try {
            if ($this->workflow->can($training, Training::TRANSITION_TO_REVIEW)) {
                $this->workflow->apply($training, Training::TRANSITION_TO_REVIEW);
                $this->entityManager->flush();
                $this->addFlash('success', 'Training moved to review.');
            } else {
                $this->addFlash('error', 'This training cannot be moved to review.');
            }
    
            return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
        } catch (\Exception $e) {
            $this->get('logger')->error('Error reviewing training: ' . $e->getMessage());
            $this->addFlash('error', 'An error occurred while reviewing the training.');
        }
    }
    
    #[Route('/training/{id}/to-confirmed', name: 'training_to_confirmed')]
    #[IsGranted("ROLE_ADMIN")]
    public function toConfirmed(Training $training): Response
    {
        $user = $this->getUser();

        try {
            // Vérification que la transition est possible
            if ($this->workflow->can($training, Training::TRANSITION_TO_CONFIRMED)) {
                $this->workflow->apply($training, Training::TRANSITION_TO_CONFIRMED);
                $this->entityManager->flush();

                $this->notificationService->createNotification('You have successfully confirmed this training: ' . $training->getTitle(), $user);

                $this->addFlash('success', 'Training confirmed.');
                return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
            } else {
                $this->addFlash('error', 'This training cannot be confirmed.');
            }
        } catch (\Exception $e) {
            $this->get('logger')->error('Error confirming training: ' . $e->getMessage());
            $this->addFlash('error', 'An error occurred while confirming the training.');
        }
    }

    #[Route('/training/{id}/to-draft', name: 'training_to_draft')]
    #[IsGranted("ROLE_ADMIN")]
    public function toDraft(Training $training): Response
    {
        try {
            if ($this->workflow->can($training, Training::TRANSITION_TO_DRAFT)) {
                $this->workflow->apply($training, Training::TRANSITION_TO_DRAFT);
                $this->entityManager->flush();
                $this->addFlash('success', 'Training reverted to draft.');
            } else {
                $this->addFlash('error', 'This training cannot be reverted to draft.');
            }
        } catch (\Exception $e) {
            $this->get('logger')->error('Error drafting training: ' . $e->getMessage());
            $this->addFlash('error', 'An error occurred while drafting the training.');
        }

        return $this->redirectToRoute('show_training', ['slug' => $training->getSlug()]);
    }
}
