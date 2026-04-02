<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\ListPetSitters;

final readonly class PetSitterView
{
    /** @param list<PetSitterInvitationView> $invitations */
    public function __construct(
        public string $id,
        public string $inviteeEmail,
        public ?string $userId,
        public string $type,
        public ?int $age,
        public ?string $phoneNumber,
        public array $invitations,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'inviteeEmail' => $this->inviteeEmail,
            'userId' => $this->userId,
            'type' => $this->type,
            'age' => $this->age,
            'phoneNumber' => $this->phoneNumber,
            'invitations' => array_map(
                static fn (PetSitterInvitationView $inv) => $inv->toArray(),
                $this->invitations,
            ),
        ];
    }
}
