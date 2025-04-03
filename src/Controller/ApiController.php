<?php

namespace App\Controller;

use App\Service\HttpClientService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ApiController extends AbstractController
{
    public function __construct(private HttpClientService $httpClientService) {}

    #[Route('/external-api', name: 'external_api')]
    public function externalApi(): Response
    {
        try {
            $results = $this->httpClientService->fetchExternalData();
        } catch (\Exception $e) {
            $this->addFlash('error', 'Failed to fetch data.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('api/index.html.twig', [
            'results' => $results,
        ]);
    }
}
