<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    public function __construct(
        private MailerInterface $mailer,
        public readonly string $administratorAddress,
        public readonly string $emailSuffix,
        private readonly string $senderAddress
    ){}

    public function dispatch(
        string $to,
        string $subject,
        string $template,
        array $context
    ){
        $message = (new TemplatedEmail())
            ->htmlTemplate($template)
            ->context($context)
            ->addFrom($this->senderAddress)
            ->addTo($to)
            ->subject($subject);

        $this->mailer->send($message);
    }
}