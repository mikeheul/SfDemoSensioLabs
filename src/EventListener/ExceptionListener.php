<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class ExceptionListener
{
    private RouterInterface $router;
    private FlashBagInterface $flashBag;

    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->flashBag = $requestStack->getSession()->getFlashBag();
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Vérifie si l'exception est une 404 (Not Found)
        if ($exception instanceof NotFoundHttpException) {
            $this->flashBag->add('error', "La page demandée n'existe pas. Vous avez été redirigé.");
            $response = new RedirectResponse($this->router->generate('app_home'));

            $event->setResponse($response);
        }
    }
}
