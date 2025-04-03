<?php

namespace App\Handler;

use App\Entity\Training;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;

class TrainingHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private SluggerInterface $slugger
    ) {}

    public function createTraining(string $title, string $description): Training
    {
        $training = new Training();
        $training->setTitle($title);
        $training->setDescription($description);

        // Generate a slug for the title
        $slug = $this->slugger->slug($training->getTitle());
        $training->setSlug($slug);

        // Persist and flush to database
        $this->entityManager->persist($training);
        $this->entityManager->flush();

        return $training;
    }
}
