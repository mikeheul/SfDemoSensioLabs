<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestLocaleListener
{
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        $locale = $request->getLocale();  // Récupère la locale active

        // Définir la timezone selon la locale
        $timezone = $this->getTimezoneForLocale($locale);
        date_default_timezone_set($timezone);  // Définit la timezone active
    }

    private function getTimezoneForLocale(string $locale): string
    {
        switch ($locale) {
            case 'fr':
                return 'Europe/Paris';
            case 'es':
                return 'Europe/Madrid';
            case 'en':
                return 'America/New_York';
            default:
                return 'UTC';  // Valeur par défaut
        }
    }
}
