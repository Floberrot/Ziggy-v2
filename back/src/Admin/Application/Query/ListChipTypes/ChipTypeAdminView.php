<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListChipTypes;

final readonly class ChipTypeAdminView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $color,
        public string $ownerId,
        public ?string $ownerEmail,
        public ?string $ownerUsername,
        public string $createdAt,
    ) {
    }

    /**
     * @return array{
     *     id: string, name: string, color: string, ownerId: string,
     *     ownerEmail: string|null, ownerUsername: string|null, createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'color' => $this->color,
            'ownerId' => $this->ownerId,
            'ownerEmail' => $this->ownerEmail,
            'ownerUsername' => $this->ownerUsername,
            'createdAt' => $this->createdAt,
        ];
    }
}
