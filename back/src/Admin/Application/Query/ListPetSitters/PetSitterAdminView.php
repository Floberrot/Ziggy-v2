<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListPetSitters;

final readonly class PetSitterAdminView
{
    public function __construct(
        public string $id,
        public string $ownerId,
        public ?string $ownerEmail,
        public ?string $ownerUsername,
        public string $inviteeEmail,
        public ?string $userId,
        public string $type,
        public ?int $age,
        public ?string $phoneNumber,
        public string $createdAt,
    ) {
    }

    /**
     * @return array{
     *     id: string, ownerId: string, ownerEmail: string|null, ownerUsername: string|null,
     *     inviteeEmail: string, userId: string|null, type: string,
     *     age: int|null, phoneNumber: string|null, createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'ownerId' => $this->ownerId,
            'ownerEmail' => $this->ownerEmail,
            'ownerUsername' => $this->ownerUsername,
            'inviteeEmail' => $this->inviteeEmail,
            'userId' => $this->userId,
            'type' => $this->type,
            'age' => $this->age,
            'phoneNumber' => $this->phoneNumber,
            'createdAt' => $this->createdAt,
        ];
    }
}
