<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use App\Entity\Notification;
use App\Service\TrainingService;
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
    #[Route('/', name: 'app_training')]
    public function index(Request $request, TrainingService $trainingService, PaginatorInterface $paginator): Response
    {
        $trainings = $trainingService->getAllTrainings();

        $pagination = $paginator->paginate(
            $trainings,
            $request->query->getInt('page', 1),
            6
        );

        return $this->render('training/index.html.twig', [
            'trainings' => $pagination,
        ]);
    }

    #[Route('/getTrainingsAPI', name: 'trainings_api')]
    public function getTrainingsAPI(SerializerInterface $serializer, TrainingRepository $trainingRepository)
    {
        $trainings = $trainingRepository->findBy([], ["title" => "ASC"]);
        $jsonContent = $serializer->serialize($trainings, 'json', ['groups' => ['training_detail']]);
        return new JsonResponse($jsonContent, 200, [], true);
    }

    #[Route('/new', name: 'new_training')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TrainingType::class, new Training());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $training = $form->getData();

            $em->persist($training);
            $em->flush();
            
            return $this->redirectToRoute('app_training');
        }
        return $this->render('training/new.html.twig', [
            'formAddTraining' => $form,
        ]);
    }

    #[Route('/{id}', name: 'show_training')]
    public function show(int $id, TrainingService $trainingService, TrainingRepository $tr): Response
    {
        $training = $trainingService->getTrainingById($id);

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

        $coursesNotInTraining = $tr->findCoursesNotInTraining($training);

        return $this->render('training/show.html.twig', [
            'training' => $training,
            'isEnrolled' => $isEnrolled,
            'coursesNotInTraining' => $coursesNotInTraining,
        ]);
    }

    #[Route('/{id}/enroll', name: 'enroll_training')]
    public function toggleEnrollment(Training $training, EntityManagerInterface $entityManager, EventDispatcherInterface $dispatcher): Response
    {
        $user = $this->getUser();

        if (in_array('ROLE_STUDENT', $user->getRoles())) {

            $isEnrolled = !$training->getTrainees()->contains($user);

            if ($training->getTrainees()->contains($user)) {
                $training->removeTrainee($user);
                $message = "You have successfully unenrolled in the training : ". $training->getTitle();
            } else {
                $training->addTrainee($user);
                $message = "You have successfully enrolled in the training : ". $training->getTitle();
            }

            $entityManager->persist($training);
            $entityManager->flush();

            $notification = new Notification();
            $notification->setMessage($message);
            $notification->setUser($user);
            $entityManager->persist($notification);
            $entityManager->flush();

            $dispatcher->dispatch(new TrainingEnrollmentEvent($user, $training, $isEnrolled), 
                $isEnrolled ? TrainingEnrollmentEvent::ENROLLED : TrainingEnrollmentEvent::UNENROLLED
        );
        }

        return $this->redirectToRoute('show_training', ['id' => $training->getId()]);
    }
}
