<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/user')]
final class UserController extends AbstractController
{
    public function __construct(private UserService $userService) {}

    #[Route('/', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route('/students', name: 'app_students_show')]
    public function showStudents(): Response
    {
        try {
            $students = $this->userService->getStudents();
        } catch (\Exception $e) {
            throw $this->createNotFoundException('No students found');
        }
        
        return $this->render('user/students.html.twig', [
            'students' => $students,
        ]);
    }

    #[Route('/teachers', name: 'app_teachers_show')]
    public function showTeachers(): Response
    {
        try {
            $teachers = $this->userService->getTeachers();
        } catch (\Exception $e) {
            throw $this->createNotFoundException('No teachers found');
        }
        
        return $this->render('user/teachers.html.twig', [
            'teachers' => $teachers,
        ]);
    }
}
