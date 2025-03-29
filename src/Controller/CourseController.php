<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


#[Route('/course')]
final class CourseController extends AbstractController
{
    #[Route('/', name: 'app_course')]
    public function index(): Response
    {
        return $this->render('course/index.html.twig', [
            'controller_name' => 'CourseController',
        ]);
    }
    
    #[Route('/new', name: 'new_course')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(CourseType::class, new Course());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $course = $form->getData();

            $em->persist($course);
            $em->flush();
            
            return $this->redirectToRoute('app_course');
        }
        return $this->render('course/new.html.twig', [
            'formAddCourse' => $form,
        ]);
    }
}
