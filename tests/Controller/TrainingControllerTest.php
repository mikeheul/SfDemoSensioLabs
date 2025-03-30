<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Training;

class TrainingControllerTest extends WebTestCase
{
    public function testCreateTraining(): void
    {
        // Créer un client HTTP (simule une requête HTTP)
        $client = static::createClient();

        // Effectuer une requête pour accéder à la page de création (vous pouvez remplacer la route par celle de votre application)
        $client->request('GET', '/training/new');

        // Vérifier si la page s'affiche correctement (code 200)
        $this->assertResponseIsSuccessful();
    }
}
