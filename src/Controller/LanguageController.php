<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class LanguageController extends AbstractController
{
    #[Route('/language/{locale}', name: 'change_language')]
    public function changeLanguage(Request $request, string $locale): RedirectResponse
    {
        // Change la locale active
        $this->get('session')->set('_locale', $locale);

        // Redirige vers la même page après avoir changé la langue
        return $this->redirect($request->headers->get('referer'));
    }
}
