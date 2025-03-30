<?php

namespace App\Event;

use App\Entity\Training;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class TrainingEnrollmentEvent extends Event
{
    public const ENROLLED = 'training.enrolled';
    public const UNENROLLED = 'training.unenrolled';

    public function __construct(
        private User $user,
        private Training $training,
        private bool $isEnrolled
    ) {}

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTraining(): Training
    {
        return $this->training;
    }

    public function isEnrolled(): bool
    {
        return $this->isEnrolled;
    }
}
