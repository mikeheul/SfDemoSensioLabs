<?php

namespace App\Twig;

use Twig\TwigFunction;
use App\Entity\Training;
use Twig\Extension\AbstractExtension;

class TrainingExtension extends AbstractExtension
{
    public function getFunctions()
    {
        return [
            new TwigFunction('training_status_confirmed', [$this, 'getConfirmedStatus']),
        ];
    }

    public function getConfirmedStatus()
    {
        return Training::PLACE_CONFIRMED;
    }
}
