<?php

namespace App\Service;

use App\Entity\Course;
use App\Repository\CourseRepository;

class CourseService
{
    public function __construct(private CourseRepository $courseRepository) {}
    
    public function getAllCourses(): array
    {
        return $this->courseRepository->findBy([], ['name' => 'ASC']);
    }

    public function getCourseById(int $id): ?Course
    {
        return $this->courseRepository->find($id);
    }

    public function getCourseBySlug(string $slug): ?Course
    {
        return $this->courseRepository->findOneBy(["slug" => $slug]);
    }
}