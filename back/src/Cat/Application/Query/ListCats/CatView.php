<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\ListCats;

final readonly class CatView
{
    /** @param list<string> $colors */
    public function __construct(
        public string $id,
        public string $name,
        public ?float $weight,
        public ?string $breed,
        public array $colors,
        public string $ownerId,
        public string $createdAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'weight' => $this->weight,
            'breed' => $this->breed,
            'colors' => $this->colors,
            'ownerId' => $this->ownerId,
            'createdAt' => $this->createdAt,
        ];
    }
}
