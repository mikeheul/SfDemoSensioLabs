<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(
    name: 'app:make-service',
    description: 'Crée un nouveau service Symfony dans src/Service/'
)]
class MakeServiceCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'Nom du service (ex: MyService)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filesystem = new Filesystem();

        // Récupérer le nom du service (soit via l'argument, soit via un prompt)
        $serviceName = $input->getArgument('name');

        if (!$serviceName) {
            $serviceName = ucfirst($io->ask('Entrez le nom du service', 'MyService'));
        } else {
            $serviceName = ucfirst($serviceName);
        }

        // Définir le chemin du fichier
        $servicePath = __DIR__ . "/../../src/Service/{$serviceName}.php";

        if ($filesystem->exists($servicePath)) {
            $io->error("Le service $serviceName existe déjà !");
            return Command::FAILURE;
        }

        // Template du fichier Service
        $serviceTemplate = <<<PHP
        <?php

        namespace App\Service;

        class $serviceName
        {
            public function __construct()
            {
                // TODO: Inject dependencies here if needed
            }

            public function doSomething(): void
            {
                // TODO: Implement service logic
            }
        }
        PHP;

        $filesystem->dumpFile($servicePath, $serviceTemplate);
        $io->success("Service $serviceName créé avec succès dans src/Service/");

        return Command::SUCCESS;
    }
}
