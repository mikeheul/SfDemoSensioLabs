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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Message\TrainingEnrollmentNotification;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[Route('/training')]
final class TrainingController extends AbstractController
{
    private NotificationService $notificationService;
    private TrainingService $trainingService;
    private PaginatorInterface $paginator;
    private EntityManagerInterface $entityManager;
    private TrainingRepository $trainingRepository;
    private EventDispatcherInterface $dispatcher;
    private MessageBusInterface $bus;
    private LoggerInterface $logger;

    public function __construct(
        NotificationService $notificationService, 
        TrainingService $trainingService, 
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
        TrainingRepository $trainingRepository,
        SerializerInterface $serializer,
        EventDispatcherInterface $dispatcher,
        MessageBusInterface $bus,
        LoggerInterface $logger)
    {
        $this->notificationService = $notificationService;
        $this->trainingService = $trainingService;
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
        $this->trainingRepository = $trainingRepository;
        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
        $this->bus = $bus;
        $this->logger = $logger;
    }

    #[Route('/', name: 'app_training')]
    public function index(Request $request): Response
    {
        try {
            $trainings = $this->trainingService->getAllTrainings();

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

    #[Route('/{id}', name: 'show_training')]
    public function show(int $id): Response
    {
        try {
            $training = $this->trainingService->getTrainingById($id);

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

        return $this->redirectToRoute('show_training', ['id' => $training->getId()]);
    }
}
