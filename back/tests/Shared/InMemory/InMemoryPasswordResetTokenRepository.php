<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\Identity\Domain\Model\PasswordResetToken;
use App\Identity\Domain\Repository\PasswordResetTokenRepository;

final class InMemoryPasswordResetTokenRepository implements PasswordResetTokenRepository
{
    /** @var array<string, PasswordResetToken> */
    private array $store = [];

    public function save(PasswordResetToken $token): void
    {
        $this->store[$token->id()] = $token;
    }

    public function findByToken(string $token): ?PasswordResetToken
    {
        foreach ($this->store as $resetToken) {
            if ($resetToken->token() === $token) {
                return $resetToken;
            }
        }

        return null;
    }

    public function markUsed(string $token): void
    {
        foreach ($this->store as $id => $resetToken) {
            if ($resetToken->token() === $token) {
                $this->store[$id] = PasswordResetToken::reconstruct(
                    id: $resetToken->id(),
                    email: $resetToken->email(),
                    token: $resetToken->token(),
                    expiresAt: $resetToken->expiresAt(),
                    used: true,
                );

                return;
            }
        }
    }
}
