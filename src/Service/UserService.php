<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;


class UserService
{
    public function __construct(private UserRepository $userRepository) {}
    
    public function getAllUsers(): array
    {
        return $this->userRepository->findBy([], ['name' => 'ASC']);
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    public function getStudents(): array
    {
        return $this->userRepository->findUsers('ROLE_STUDENT');
    }

    public function getTeachers(): array
    {
        return $this->userRepository->findUsers('ROLE_TEACHER');
    }
}