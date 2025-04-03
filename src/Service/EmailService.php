<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    public function sendEmail(
        string $to,
        string $subject,
        string $template,
        array $context = [],
        ?string $pdfContent = null
    ): void {
        $emailContent = $this->twig->render($template, $context);

        $email = (new Email())
            ->from('noreply@formation.com')
            ->to($to)
            ->subject($subject)
            ->html($emailContent);

        if ($pdfContent) {
            $email->attach($pdfContent, 'confirmation_inscription.pdf', 'application/pdf');
        }

        $this->mailer->send($email);
    }
}
