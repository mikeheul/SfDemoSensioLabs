<?php

namespace App\Service;

use App\Entity\Course;
use App\Repository\CourseRepository;

class CourseService
{
    private CourseRepository $courseRepository;

    public function __construct(courseRepository $courseRepository)
    {
        $this->courseRepository = $courseRepository;
    }
    
    public function getAllCourses(): array
    {
        return $this->courseRepository->findBy([], ['name' => 'ASC']);
    }

    public function getCourseById(int $id): ?Course
    {
        return $this->courseRepository->find($id);
    }
}