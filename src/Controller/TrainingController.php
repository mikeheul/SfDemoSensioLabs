<?php

namespace App\Controller;

use App\Entity\Training;
use App\Form\TrainingType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/training')]
final class TrainingController extends AbstractController
{
    #[Route('/', name: 'app_training')]
    public function index(): Response
    {
        return $this->render('training/index.html.twig', [
            'controller_name' => 'TrainingController',
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
}
