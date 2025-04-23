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

// Route to handle all course-related actions
// #[Route('/course')]
#[Route('/{_locale}/course', requirements: ['_locale' => 'en|fr|es|de'], defaults: ['_locale' => 'en'])]
final class CourseController extends AbstractController
{
    // Constructor dependency injection for services
    public function __construct(
        private CourseService $courseService, 
        private EntityManagerInterface $entityManager,
        private SerializerInterface $serializerInterface,
        private SluggerInterface $slugger)
    {}

    // Route to display all courses
    #[Route('/', name: 'app_course')]
    public function index(): Response
    {
        try {
            // Fetch all courses via the service
            $courses = $this->courseService->getAllCourses();
        } catch (\Exception $e) {
            // Handle error if fetching courses fails
            $this->addFlash('error', 'An error occurred while fetching courses.');
            $courses = [];
        }

        // Render the course list page
        return $this->render('course/index.html.twig', [
            'courses' => $courses,
        ]);
    }
    
    // Route to create a new course
    #[Route('/new', name: 'new_course')]
    public function new(Request $request): Response
    {
        // Create and handle form submission
        $form = $this->createForm(CourseType::class, new Course());
        $form->handleRequest($request);

        // Process the form submission
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Get the data from the form
                $course = $form->getData();
                
                // Generate and set a slug based on course name
                $slug = $this->slugger->slug($course->getName());
                $course->setSlug($slug);
                
                // Persist the new course entity and save it to the database
                $this->entityManager->persist($course);
                $this->entityManager->flush();

                // Show success message and redirect
                $this->addFlash('success', 'Course successfully created.');
                return $this->redirectToRoute('app_course');
            } catch (ORMException $e) {
                // Handle ORM exception (e.g., database issues)
                $this->addFlash('error', 'Failed to save the course.');
            } catch (\Exception $e) {
                // Catch any other exception
                $this->addFlash('error', 'An unexpected error occurred.');
            }
        }

        // Render the form page for creating a new course
        return $this->render('course/new.html.twig', [
            'formAddCourse' => $form,
        ]);
    }
    
    // Route to display details of a single course
    #[Route('/{slug}', name: 'show_course')]
    public function show(string $slug): Response
    {
        try {
            // Fetch the course by its slug
            $course = $this->courseService->getCourseBySlug($slug);

            // If course is not found, throw an exception
            if (!$course) {
                throw new NotFoundHttpException('Course not found');
            }

        } catch (NotFoundHttpException $e) {
            // Handle course not found exception
            $this->addFlash('error', 'Course not found.');
            return $this->redirectToRoute('app_course');
        } catch (\Exception $e) {
            // Handle other types of errors
            $this->addFlash('error', 'An error occurred while fetching the course.');
            return $this->redirectToRoute('app_course');
        }

        // Render the page with course details
        return $this->render('course/show.html.twig', [
            'course' => $course,
        ]);
    }
}