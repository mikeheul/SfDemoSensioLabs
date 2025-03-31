<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    #[Route(path: '/profile', name: 'app_profile')]
    public function profile()
    {
        try {
            $user = $this->getUser();
    
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to access your profile.');
                return $this->redirectToRoute('app_login');
            }
    
            return $this->render('security/profile.html.twig', ['user' => $user]);
    
        } catch (\Exception $e) {
            $this->addFlash('error', 'An unexpected error occurred.');
            return $this->redirectToRoute('app_home'); 
        }
    }
}
