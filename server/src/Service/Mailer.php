<?php
declare(strict_types=1);

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

readonly class Mailer
{
    public function __construct(
        private MailerInterface $mailer,
        public string $administratorAddress,
        public string $emailSuffix,
        private string $senderAddress
    ){}

    /**
     * Dispatch an email. Simple wrapper over Symfony's mailer.
     *
     * @param string $to
     * The address to dispatch to.
     *
     * @param string $subject
     * The email subject.
     *
     * @param string $template
     * The twig template to render as the message's HTML body.
     *
     * @param array<string, mixed> $context
     * Context to provide to the templating engine.
     *
     * @return void
     * @throws TransportExceptionInterface
     */
    public function dispatch(
        string $to,
        string $subject,
        string $template,
        array $context
    ): void
    {
        $message = (new TemplatedEmail())
            ->htmlTemplate($template)
            ->context($context)
            ->addFrom($this->senderAddress)
            ->addTo($to)
            ->subject($subject);

        $this->mailer->send($message);
    }
}