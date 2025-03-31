<?php

namespace App\Notification;

use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\Recipient\Recipient;

class UserDeactivatedNotification extends Notification
{
    public function __construct()
    {
        parent::__construct('Votre compte a été désactivé.');
    }

    public function toEmailBody(): string
    {
        return 'Bonjour, votre compte a été désactivé. Si vous avez des questions, veuillez nous contacter.';
    }
}
