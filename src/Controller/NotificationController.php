<?php

namespace App\Controller;

use App\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

final class NotificationController extends AbstractController
{
    private NotificationRepository $notificationRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        NotificationRepository $notificationRepository, 
        EntityManagerInterface $entityManager)
    {
        $this->notificationRepository = $notificationRepository;
        $this->entityManager = $entityManager;
    }

    #[Route('/notification', name: 'app_notification')]
    public function index(UserInterface $user): Response
    {
        try {
            $notifications = $this->notificationRepository->findBy(
                ['user' => $user, 'isRead' => false], 
                ['id' => 'DESC']
            );
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while fetching notifications.');
            return $this->redirectToRoute('app_home'); // Redirection en cas d'erreur
        }
    
        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/mark-as-read/{id}', name: 'mark_as_read')]
    public function markAsRead(Notification $notification): RedirectResponse
    {
        try {
            $notification->setIsRead(true);
            $this->entityManager->flush();
            $this->addFlash('success', 'Notification marked as read.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'An error occurred while marking the notification as read.');
        }
    
        return $this->redirectToRoute('app_notification');
    }
}
