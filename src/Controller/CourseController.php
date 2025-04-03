<?php

namespace App\Controller;

use App\Entity\Course;
use App\Form\CourseType;
use App\Service\CourseService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


#[Route('/course')]
final class CourseController extends AbstractController
{
    public function __construct(
        private CourseService $courseService, 
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializerInterface,
        private SluggerInterface $slugger)
    {}

    #[Route('/', name: 'app_course')]
    public function index(): Response
    {
        try {
            $courses = $this->courseService->getAllCourses();
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while fetching courses.');
            $courses = [];
        }

        return $this->render('course/index.html.twig', [
            'courses' => $courses,
        ]);
    }
    
    #[Route('/new', name: 'new_course')]
    public function new(Request $request): Response
    {
        $form = $this->createForm(CourseType::class, new Course());
        $form->handleRequest($request);

        
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $course = $form->getData();
                
                $slug = $this->slugger->slug($course->getName());
                $course->setSlug($slug);
                
                $this->entityManager->persist($course);
                $this->entityManager->flush();

                $this->addFlash('success', 'Course successfully created.');
                return $this->redirectToRoute('app_course');
            } catch (ORMException $e) {
                $this->addFlash('error', 'Failed to save the course.');
            } catch (\Exception $e) {
                $this->addFlash('error', 'An unexpected error occurred.');
            }
        }
        return $this->render('course/new.html.twig', [
            'formAddCourse' => $form,
        ]);
    }
    
    #[Route('/{slug}', name: 'show_course')]
    public function show(string $slug): Response
    {
        try {
            $course = $this->courseService->getCourseBySlug($slug);

            if (!$course) {
                throw new NotFoundHttpException('Course not found');
            }

        } catch (NotFoundHttpException $e) {
            $this->addFlash('error', 'Course not found.');
            return $this->redirectToRoute('app_course');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while fetching the course.');
            return $this->redirectToRoute('app_course');
        }

        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }


}
