<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

final readonly class PasswordResetToken
{
    public function __construct(
        private string $id,
        private Email $email,
        private string $token,
        private \DateTimeImmutable $expiresAt,
        private bool $used,
    ) {
    }

    public static function create(string $id, Email $email): self
    {
        return new self(
            id: $id,
            email: $email,
            token: bin2hex(random_bytes(32)),
            expiresAt: new \DateTimeImmutable('+1 hour'),
            used: false,
        );
    }

    public static function reconstruct(
        string $id,
        Email $email,
        string $token,
        \DateTimeImmutable $expiresAt,
        bool $used,
    ): self {
        return new self(
            id: $id,
            email: $email,
            token: $token,
            expiresAt: $expiresAt,
            used: $used,
        );
    }

    public function id(): string
    {
        return $this->id;
    }
    public function email(): Email
    {
        return $this->email;
    }
    public function token(): string
    {
        return $this->token;
    }
    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function isUsed(): bool
    {
        return $this->used;
    }
    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }
}
