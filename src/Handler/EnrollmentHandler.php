<?php

namespace App\Handler;

use App\Entity\Training;
use App\Entity\User;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;

class EnrollmentHandler
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private NotificationService $notificationService)
    {}

    public function toggleEnrollment(User $user, Training $training): void
    {
        $isEnrolled = !$training->getTrainees()->contains($user);

        if ($isEnrolled) {
            $training->addTrainee($user);
            $this->notificationService->createNotification('You are now enrolled in this training: ' . $training->getTitle(), $user);
        } else {
            $training->removeTrainee($user);
            $this->notificationService->createNotification('You have successfully unenrolled from the training: ' . $training->getTitle(), $user);
        }

        $this->entityManager->persist($training);
        $this->entityManager->flush();
    }
}
