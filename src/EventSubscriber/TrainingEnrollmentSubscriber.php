<?php

namespace App\EventSubscriber;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use App\Service\PdfService;
use App\Service\EmailService;
use Symfony\Component\Mime\Email;
use App\Event\TrainingEnrollmentEvent;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TrainingEnrollmentSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private EmailService $emailService,
        private PdfService $pdfService
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

        $pdfContent = $this->pdfService->generatePdf('pdf/training_confirmation.html.twig', [
            'user' => $user,
            'training' => $training,
        ]);

        $this->emailService->sendEmail(
            $user->getEmail(),
            $subject,
            'emails/training_notification.html.twig',
            ['user' => $user, 'training' => $training, 'isEnrolled' => $event->isEnrolled()],
            $pdfContent
        );
    }
}
