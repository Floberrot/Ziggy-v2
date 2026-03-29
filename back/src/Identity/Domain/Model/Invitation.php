<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

final readonly class Invitation
{
    public function __construct(
        private string $id,
        private UserId $ownerId,
        private Email $inviteeEmail,
        private string $catId,
        private string $token,
        private \DateTimeImmutable $expiresAt,
        private bool $accepted,
    ) {
    }

    public static function create(
        string $id,
        UserId $ownerId,
        Email $inviteeEmail,
        string $catId,
    ): self {
        return new self(
            id: $id,
            ownerId: $ownerId,
            inviteeEmail: $inviteeEmail,
            catId: $catId,
            token: bin2hex(random_bytes(32)),
            expiresAt: new \DateTimeImmutable('+7 days'),
            accepted: false,
        );
    }

    public static function reconstruct(
        string $id,
        UserId $ownerId,
        Email $inviteeEmail,
        string $catId,
        string $token,
        \DateTimeImmutable $expiresAt,
        bool $accepted,
    ): self {
        return new self(
            id: $id,
            ownerId: $ownerId,
            inviteeEmail: $inviteeEmail,
            catId: $catId,
            token: $token,
            expiresAt: $expiresAt,
            accepted: $accepted,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function ownerId(): UserId
    {
        return $this->ownerId;
    }

    public function inviteeEmail(): Email
    {
        return $this->inviteeEmail;
    }

    public function catId(): string
    {
        return $this->catId;
    }

    public function token(): string
    {
        return $this->token;
    }

    public function expiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isAccepted(): bool
    {
        return $this->accepted;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }
}
