<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Notification;

use App\Identity\Application\Port\InvitationMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class MailInvitationMailer implements InvitationMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private string $frontendBaseUrl,
    ) {
    }

    public function sendInvitation(string $inviteeEmail, string $token): void
    {
        $link = sprintf('%s/invitation/accept?token=%s', rtrim($this->frontendBaseUrl, '/'), $token);

        $email = (new Email())
            ->to($inviteeEmail)
            ->subject('You have been invited to Ziggy')
            ->html(sprintf(
                '<p>Hi,</p>
                <p>You have been invited to join Ziggy as a pet sitter.</p>
                <p><a href="%s">Click here to accept the invitation</a></p>
                <p>This link expires in 7 days.</p>',
                htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            ));

        $this->mailer->send($email);
    }
}
