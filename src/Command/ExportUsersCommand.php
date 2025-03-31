<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:export-users',
    description: 'Exporte la liste des utilisateurs en CSV'
)]
class ExportUsersCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this
            ->addOption('path', null, InputOption::VALUE_OPTIONAL, 'Chemin du fichier de sortie', 'var/export/users.csv');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();
        $path = $input->getOption('path');

        // Vérifier que le dossier existe, sinon le créer
        $directory = dirname($path);
        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory);
        }

        // Récupérer les utilisateurs
        $users = $this->entityManager->getRepository(User::class)->findAll();

        if (!$users) {
            $io->warning('Aucun utilisateur trouvé.');
            return Command::SUCCESS;
        }

        // Ouvrir le fichier CSV en écriture
        $file = fopen($path, 'w');
        if (!$file) {
            $io->error("Impossible d'écrire dans le fichier : $path");
            return Command::FAILURE;
        }

        // Écrire l'en-tête du fichier CSV
        fputcsv($file, ['ID', 'Email', 'Roles', 'Trainings']);

        // Écrire les données des utilisateurs
        foreach ($users as $user) {
            $roles = implode(', ', $user->getRoles()); // Convertir array en string
            $trainings = $user->getTrainings()
                ? implode(' | ', $user->getTrainings()->map(fn($t) => $t->getTitle())->toArray()) // Convertir Collection en string
                : 'Aucun';

            fputcsv($file, [
                $user->getId(),
                $user->getEmail(),
                $roles,
                $trainings
            ]);
        }

        fclose($file);

        $io->success("Export des utilisateurs réussi ! Fichier créé : $path");
        return Command::SUCCESS;
    }
}
