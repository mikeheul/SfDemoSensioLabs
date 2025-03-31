<?php

// src/Twig/NotificationExtension.php
namespace App\Twig;

use App\Repository\NotificationRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationExtension extends AbstractExtension
{
    private NotificationRepository $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('unread_notifications_count', [$this, 'getUnreadNotificationsCount']),
        ];
    }

    public function getUnreadNotificationsCount($user): int
    {
        if ($user) {
            // Récupérer le nombre de notifications non lues pour l'utilisateur
            $notifications = $this->notificationRepository->getUserNotifications($user);
            return count($notifications);
        }
        return 0;
    }
}
