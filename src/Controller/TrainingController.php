<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use App\Service\TrainingService;
use App\Repository\TrainingRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/training')]
final class TrainingController extends AbstractController
{
    #[Route('/', name: 'app_training')]
    public function index(TrainingService $trainingService): Response
    {
        $trainings = $trainingService->getAllTrainings();
        return $this->render('training/index.html.twig', [
            'trainings' => $trainings,
        ]);
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
    public function toggleEnrollment(Training $training, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (in_array('ROLE_STUDENT', $user->getRoles())) {
            if ($training->getTrainees()->contains($user)) {
                $training->removeTrainee($user);
            } else {
                $training->addTrainee($user);
            }

            $entityManager->persist($training);
            $entityManager->flush();
        }

        return $this->redirectToRoute('show_training', ['id' => $training->getId()]);
    }
}
