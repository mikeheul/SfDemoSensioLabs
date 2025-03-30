<?php

namespace App\Tests\Service;

use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Service\CourseService;
use PHPUnit\Framework\TestCase;

class CourseServiceTest extends TestCase
{
    private CourseRepository $courseRepository;
    private CourseService $courseService;

    protected function setUp(): void
    {
        // Créer un mock de CourseRepository
        $this->courseRepository = $this->createMock(CourseRepository::class);

        // Créer l'instance du service avec le mock
        $this->courseService = new CourseService($this->courseRepository);
    }

    public function testGetAllCourses(): void
    {
        // Créer des données fictives pour les tests
        $course1 = new Course();
        $course1->setName('Course 1');
        
        $course2 = new Course();
        $course2->setName('Course 2');

        // Configurer le mock pour la méthode findBy() pour renvoyer un tableau de cours
        $this->courseRepository
            ->method('findBy')
            ->willReturn([$course1, $course2]);

        // Appeler la méthode à tester
        $courses = $this->courseService->getAllCourses();

        // Vérifier que la méthode renvoie bien deux cours
        $this->assertCount(2, $courses, 'Expected 2 courses to be returned');
        $this->assertSame('Course 1', $courses[0]->getName(), 'First course name should be "Course 1"');
        $this->assertSame('Course 2', $courses[1]->getName(), 'Second course name should be "Course 2"');
    }

    public function testGetCourseById(): void
    {
        // Créer un objet Course fictif pour le test
        $course = new Course();
        $course->setName('Course 1');
        $course->setId(1);

        // Configurer le mock pour la méthode find() pour renvoyer ce cours particulier
        $this->courseRepository
            ->method('find')
            ->with(1)
            ->willReturn($course);

        // Appeler la méthode à tester
        $result = $this->courseService->getCourseById(1);

        // Vérifier que le cours renvoyé est celui que nous avons simulé
        $this->assertSame('Course 1', $result->getName(), 'Course name should be "Course 1"');
        $this->assertSame(1, $result->getId(), 'Course ID should be 1');
    }

    public function testGetCourseByIdNotFound(): void
    {
        // Configurer le mock pour la méthode find() pour renvoyer null si l'ID n'est pas trouvé
        $this->courseRepository
            ->method('find')
            ->with(999)
            ->willReturn(null);

        // Appeler la méthode à tester
        $result = $this->courseService->getCourseById(999);

        // Vérifier que la méthode retourne null
        $this->assertNull($result, 'Expected result to be null for non-existing course');
    }
}
