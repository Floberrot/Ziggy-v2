<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\ListPetSitters;

final readonly class PetSitterInvitationView
{
    public function __construct(
        public string $id,
        public string $catId,
        public string $token,
        public bool $accepted,
        public bool $declined,
        public bool $expired,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'catId' => $this->catId,
            'token' => $this->token,
            'accepted' => $this->accepted,
            'declined' => $this->declined,
            'expired' => $this->expired,
        ];
    }
}
