<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

use App\Identity\Domain\Exception\InvitationAlreadyAcceptedException;
use App\Identity\Domain\Exception\InvitationAlreadyDeclinedException;
use App\Identity\Domain\Exception\InvitationExpiredException;

final class Invitation
{
    private bool $declined;

    public function __construct(
        private readonly string $id,
        private readonly UserId $ownerId,
        private readonly Email $inviteeEmail,
        private readonly string $catId,
        private readonly string $token,
        private readonly \DateTimeImmutable $expiresAt,
        private readonly bool $accepted,
        bool $declined,
    ) {
        $this->declined = $declined;
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
            declined: false,
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
        bool $declined,
    ): self {
        return new self(
            id: $id,
            ownerId: $ownerId,
            inviteeEmail: $inviteeEmail,
            catId: $catId,
            token: $token,
            expiresAt: $expiresAt,
            accepted: $accepted,
            declined: $declined,
        );
    }

    public function decline(): void
    {
        if ($this->accepted) {
            throw new InvitationAlreadyAcceptedException();
        }

        if ($this->isExpired()) {
            throw new InvitationExpiredException();
        }

        if ($this->declined) {
            throw new InvitationAlreadyDeclinedException();
        }

        $this->declined = true;
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

    public function isDeclined(): bool
    {
        return $this->declined;
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new \DateTimeImmutable();
    }
}
