<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Training;
use App\Repository\TrainingRepository;

class TrainingService
{
    public function __construct(private TrainingRepository $trainingRepository) {}
    
    public function getAllTrainings(): array
    {
        return $this->trainingRepository->findBy([], ['startDate' => 'ASC']);
    }

    public function getAllTrainingsConfirmed(): array
    {
        return $this->trainingRepository->findBy(['status' => 'confirmed'], ['startDate' => 'ASC']);
    }

    public function getTrainingById(int $id): ?Training
    {
        return $this->trainingRepository->find($id);
    }

    public function getTrainingBySlug(string $slug): ?Training
    {
        return $this->trainingRepository->findOneBy(["slug" => $slug]);
    }

    public function isUserEnrolled(Training $training, User $user): bool
    {
        if ($user && in_array('ROLE_STUDENT', $user->getRoles())) {
            return $training->getTrainees()->contains($user);
        }
        return false;
    }

    public function sortTraineesByLastName(PersistentCollection $trainees): array
    {
        $traineesArray = $trainees->toArray();
        usort($traineesArray, function($a, $b) {
            return strcmp($a->getLastName(), $b->getLastName());
        });
        return $traineesArray;
    }

    // Récupérer les cours non associés à la formation
    public function getCoursesNotInTraining(Training $training): array
    {
        return $this->trainingRepository->findCoursesNotInTraining($training);
    }
}