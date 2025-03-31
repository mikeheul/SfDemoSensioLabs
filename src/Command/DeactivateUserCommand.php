<?php

namespace App\Command;

use App\Entity\User;
use App\Entity\Notification;
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

    public function __construct(EntityManagerInterface $entityManager, NotifierInterface $notifier)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->notifier = $notifier;
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
            $io->error("Aucun utilisateur trouvé avec l'email $email.");
            return Command::FAILURE;
        }

        // Désactivation de l'utilisateur
        $user->setIsActive(false);
        $this->entityManager->flush();

        $notification = new Notification();
        $notification->setMessage('Votre compte a été désactivé.');
        $notification->setUser($user);
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $io->success("L'utilisateur $email a été désactivé avec succès.");
        return Command::SUCCESS;
    }
}
