<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Symfony\Component\HttpFoundation\RequestStack;

class TranslationExtension extends AbstractExtension
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('translate', [$this, 'translate']),
        ];
    }

    public function translate(object $entity, string $field): ?string
    {
        $locale = $this->requestStack->getCurrentRequest()?->getLocale() ?? 'en';

        $method = 'get' . ucfirst($field) . ucfirst($locale);

        if (method_exists($entity, $method)) {
            return $entity->$method();
        }

        $fallbackMethod = 'get' . ucfirst($field) . 'Fr';
        return method_exists($entity, $fallbackMethod) ? $entity->$fallbackMethod() : null;
    }
}
