<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class NotificationController extends AbstractController
{
    #[Route('/notification', name: 'app_notification')]
    public function index(NotificationRepository $notificationRepository, UserInterface $user): Response
    {
        $notifications = $notificationRepository->findBy(['user' => $user, 'isRead' => false], ['id' => 'DESC']);
        
        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/mark-as-read/{id}', name: 'mark_as_read')]
    public function markAsRead(Notification $notification, EntityManagerInterface $em): RedirectResponse
    {
        $notification->setIsRead(true);
        $em->flush();

        return $this->redirectToRoute('app_notification');
    }
}
