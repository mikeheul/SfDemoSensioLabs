<?php

namespace App\Message;

class TrainingEnrollmentNotification
{
    private string $email;
    private string $trainingTitle;

    public function __construct(string $email, string $trainingTitle)
    {
        $this->email = $email;
        $this->trainingTitle = $trainingTitle;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getTrainingTitle(): string
    {
        return $this->trainingTitle;
    }
}
