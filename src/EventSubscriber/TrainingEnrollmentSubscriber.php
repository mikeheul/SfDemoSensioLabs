<?php

namespace App\EventSubscriber;

use App\Event\TrainingEnrollmentEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class TrainingEnrollmentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            TrainingEnrollmentEvent::ENROLLED => 'onTrainingEnrolled',
            TrainingEnrollmentEvent::UNENROLLED => 'onTrainingUnenrolled',
        ];
    }

    public function onTrainingEnrolled(TrainingEnrollmentEvent $event): void
    {
        $this->sendEmail($event, 'Inscription confirmée à la formation');
    }

    public function onTrainingUnenrolled(TrainingEnrollmentEvent $event): void
    {
        $this->sendEmail($event, 'Désinscription confirmée de la formation');
    }

    private function sendEmail(TrainingEnrollmentEvent $event, string $subject): void
    {
        $user = $event->getUser();
        $training = $event->getTraining();

        $emailContent = $this->twig->render('emails/training_notification.html.twig', [
            'user' => $user,
            'training' => $training,
            'isEnrolled' => $event->isEnrolled(),
        ]);

        $email = (new Email())
            ->from('noreply@formation.com')
            ->to($user->getEmail())
            ->subject($subject)
            ->html($emailContent);

        $this->mailer->send($email);
    }
}
