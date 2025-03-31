<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use App\Entity\Notification;
use App\Service\TrainingService;
use App\Service\NotificationService;
use App\Event\TrainingEnrollmentEvent;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/training')]
final class TrainingController extends AbstractController
{
    private NotificationService $notificationService;
    private TrainingService $trainingService;
    private PaginatorInterface $paginator;
    private EntityManagerInterface $entityManager;
    private TrainingRepository $trainingRepository;
    private EventDispatcherInterface $dispatcher;

    public function __construct(
        NotificationService $notificationService, 
        TrainingService $trainingService, 
        PaginatorInterface $paginator,
        EntityManagerInterface $entityManager,
        TrainingRepository $trainingRepository,
        SerializerInterface $serializer,
        EventDispatcherInterface $dispatcher)
    {
        $this->notificationService = $notificationService;
        $this->trainingService = $trainingService;
        $this->paginator = $paginator;
        $this->entityManager = $entityManager;
        $this->trainingRepository = $trainingRepository;
        $this->serializer = $serializer;
        $this->dispatcher = $dispatcher;
    }

    #[Route('/', name: 'app_training')]
    public function index(Request $request): Response
    {
        $trainings = $this->trainingService->getAllTrainings();

        $pagination = $this->paginator->paginate(
            $trainings,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('training/index.html.twig', [
            'trainings' => $pagination,
        ]);
    }

    #[Route('/getTrainingsAPI', name: 'trainings_api')]
    public function getTrainingsAPI()
    {
        $trainings = $this->trainingRepository->findBy([], ["title" => "ASC"]);
        $jsonContent = $this->serializer->serialize($trainings, 'json', ['groups' => ['training_detail']]);
        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/new', name: 'new_training')]
    public function new(Request $request): Response
    {
        $form = $this->createForm(TrainingType::class, new Training());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $training = $form->getData();

            $this->entityManager->persist($training);
            $this->entityManager->flush();
            
            return $this->redirectToRoute('app_training');
        }
        return $this->render('training/new.html.twig', [
            'formAddTraining' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show_training')]
    public function show(int $id): Response
    {
        $training = $this->trainingService->getTrainingById($id);

        if (!$training) {
            $this->addFlash('error', 'Training not found.');
            return $this->redirectToRoute('app_training');
        }

        $user = $this->getUser();

        if ($user) {
            if (in_array('ROLE_STUDENT', $user->getRoles())) {
                $isEnrolled = $training->getTrainees()->contains($user);
            } else {
                $isEnrolled = false;
            }
        } else {
            $isEnrolled = false;
        }

        $coursesNotInTraining = $this->trainingRepository->findCoursesNotInTraining($training);

        return $this->render('training/show.html.twig', [
            'training' => $training,
            'isEnrolled' => $isEnrolled,
            'coursesNotInTraining' => $coursesNotInTraining,
        ]);
    }

    #[Route('/{id}/enroll', name: 'enroll_training')]
    public function toggleEnrollment(Training $training): Response
    {
        $user = $this->getUser();

        if (in_array('ROLE_STUDENT', $user->getRoles())) {

            $isEnrolled = !$training->getTrainees()->contains($user);

            if ($training->getTrainees()->contains($user)) {
                $training->removeTrainee($user);
                $this->notificationService->createNotification('You have successfully unenrolled from the training : ' . $training->getTitle(), $user);
            } else {
                $training->addTrainee($user);
                $this->notificationService->createNotification('You are already enrolled in this training : ' . $training->getTitle(), $user);
            }

            $this->entityManager->persist($training);
            $this->entityManager->flush();

            $this->dispatcher->dispatch(new TrainingEnrollmentEvent($user, $training, $isEnrolled), 
                $isEnrolled ? TrainingEnrollmentEvent::ENROLLED : TrainingEnrollmentEvent::UNENROLLED
        );
        }

        return $this->redirectToRoute('show_training', ['id' => $training->getId()]);
    }
}
