<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RequestPasswordReset;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\PasswordResetToken;
use App\Identity\Domain\Repository\PasswordResetTokenRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email as MimeEmail;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RequestPasswordResetHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordResetTokenRepository $passwordResetTokenRepository,
        private MailerInterface $mailer,
        private string $frontendBaseUrl,
    ) {
    }

    public function __invoke(RequestPasswordResetCommand $command): void
    {
        // Always behave identically to avoid email enumeration.
        $user = $this->userRepository->findByEmail(new Email($command->email));

        if (null === $user) {
            return;
        }

        $resetToken = PasswordResetToken::create(
            id: Uuid::v7()->toRfc4122(),
            email: new Email($command->email),
        );

        $this->passwordResetTokenRepository->save($resetToken);

        $resetLink = sprintf('%s/reset-password?token=%s', rtrim($this->frontendBaseUrl, '/'), $resetToken->token());

        $email = (new MimeEmail())
            ->to($command->email)
            ->subject('Reset your Ziggy password')
            ->html(sprintf(
                '<p>Hi,</p>
                <p>You requested a password reset for your Ziggy account.</p>
                <p><a href="%s">Click here to reset your password</a></p>
                <p>This link expires in 1 hour. If you did not request this, you can safely ignore this email.</p>',
                htmlspecialchars($resetLink, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            ));

        $this->mailer->send($email);
    }
}
