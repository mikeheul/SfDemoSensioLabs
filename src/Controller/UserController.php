<?php

namespace App\Controller;

use App\Service\UserService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// #[Route('/user')]
#[Route('/{_locale}/user', requirements: ['_locale' => 'en|fr|es|de'], defaults: ['_locale' => 'en'])]
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

    #[Route('/student/{id}', name: 'app_student_show')]
    public function showUser(int $id): Response
    {
        try {
            $student = $this->userService->getUserById($id);

            if (!$student) {
                throw new NotFoundHttpException('Student not found.');
            }
        } catch (NotFoundHttpException $e) {
            // Handle student not found exception
            $this->addFlash('error', 'Student not found.');
            return $this->redirectToRoute('app_students_show');
        } catch (\Exception $e) {
            // Handle other types of errors
            $this->addFlash('error', 'An error occurred while fetching the student details.');
            return $this->redirectToRoute('app_students_show');
        }
        
        return $this->render('user/student.html.twig', [
            'student' => $student,
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
