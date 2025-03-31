<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Notification;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use App\Notification\UserDeactivatedNotification;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:deactivate-user',
    description: 'Désactive un utilisateur'
)]
class DeactivateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private NotifierInterface $notifier;
    private NotificationService $notificationService;

    public function __construct(EntityManagerInterface $entityManager, NotifierInterface $notifier, NotificationService $notificationService)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
        $this->notificationService = $notificationService;
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email de l\'utilisateur à désactiver');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            $io->error("No user found with email $email.");
            return Command::FAILURE;
        }

        // Désactivation de l'utilisateur
        $user->setIsActive(false);
        $this->entityManager->flush();

        $this->notificationService->createNotification("Your Account is deactivated", $user);

        $io->success("User $email successfully deactivated");
        return Command::SUCCESS;
    }
}
