<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private Security $security
    ) {}

    public function createNotification(string $message, User $user = null): void
    {
        if ($user) {

            $notification = new Notification();
            $notification->setMessage($message);
            $notification->setUser($user);

            $this->entityManager->persist($notification);
            $this->entityManager->flush();
        }
    }
}