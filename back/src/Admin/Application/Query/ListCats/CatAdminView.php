<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListCats;

final readonly class CatAdminView
{
    public function __construct(
        public string $id,
        public string $name,
        public ?float $weight,
        public ?string $breed,
        /** @var list<string> */
        public array $colors,
        public string $ownerId,
        public ?string $ownerEmail,
        public ?string $ownerUsername,
        public string $createdAt,
    ) {
    }

    /**
     * @return array{
     *     id: string, name: string, weight: float|null, breed: string|null,
     *     colors: list<string>, ownerId: string, ownerEmail: string|null,
     *     ownerUsername: string|null, createdAt: string
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'breed' => $this->breed,
            'colors' => $this->colors,
            'ownerId' => $this->ownerId,
            'ownerEmail' => $this->ownerEmail,
            'ownerUsername' => $this->ownerUsername,
            'createdAt' => $this->createdAt,
        ];
    }
}
