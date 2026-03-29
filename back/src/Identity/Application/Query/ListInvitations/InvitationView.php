<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\ListInvitations;

final readonly class InvitationView
{
    public function __construct(
        public string $id,
        public string $inviteeEmail,
        public string $catId,
        public string $token,
        public string $expiresAt,
        public bool $accepted,
        public bool $expired,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'inviteeEmail' => $this->inviteeEmail,
            'catId' => $this->catId,
            'token' => $this->token,
            'expiresAt' => $this->expiresAt,
            'accepted' => $this->accepted,
            'expired' => $this->expired,
        ];
    }
}
