<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home_redirect')]
    public function redirectToHome(): Response
    {
        return $this->redirectToRoute('app_home', [
            '_locale' => 'en',
        ]);
    }

    #[Route('/{_locale}/home', name: 'app_home', requirements: ['_locale' => 'en|fr|es|de'], defaults: ['_locale' => 'en'])]
    public function index(): Response
    {
        return $this->render('home/index.html.twig', []);
    }
}
