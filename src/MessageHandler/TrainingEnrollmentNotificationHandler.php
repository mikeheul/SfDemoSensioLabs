<?php

namespace App\MessageHandler;

use App\Message\TrainingEnrollmentNotification;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class TrainingEnrollmentNotificationHandler implements MessageHandlerInterface
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(TrainingEnrollmentNotification $message)
    {
        $email = (new Email())
            ->from('no-reply@yourapp.com')
            ->to($message->getEmail())
            ->subject('Enrolment in ' . $message->getTrainingTitle())
            ->text('You have successfully enrolled in ' . $message->getTrainingTitle());

        $this->mailer->send($email);
    }
}
