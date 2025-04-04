<?php

namespace App\Service;

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
}