<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\ResetPassword;

use App\Identity\Domain\Exception\InvalidPasswordResetTokenException;
use App\Identity\Domain\Exception\ResetTokenAlreadyUsedException;
use App\Identity\Domain\Exception\ResetTokenExpiredException;
use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Repository\PasswordResetTokenRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class ResetPasswordHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PasswordResetTokenRepository $passwordResetTokenRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(ResetPasswordCommand $command): void
    {
        $resetToken = $this->passwordResetTokenRepository->findByToken($command->token);

        if (null === $resetToken) {
            throw new InvalidPasswordResetTokenException($command->token);
        }

        if ($resetToken->isUsed()) {
            throw new ResetTokenAlreadyUsedException($command->token);
        }

        if ($resetToken->isExpired()) {
            throw new ResetTokenExpiredException($command->token);
        }

        $user = $this->userRepository->findByEmail($resetToken->email());

        if (null === $user) {
            throw new UserNotFoundException($resetToken->email()->value());
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            new InMemoryUser($resetToken->email()->value(), ''),
            $command->newPassword,
        );

        $user->changePassword($hashedPassword);
        $this->userRepository->save($user);
        $this->passwordResetTokenRepository->markUsed($command->token);
    }
}
