<?php

declare(strict_types=1);

namespace App\ChipType\Application\Query\ListChipTypes;

final readonly class ChipTypeView
{
    public function __construct(
        public string $id,
        public string $name,
        public string $color,
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
            'color' => $this->color,
            'ownerId' => $this->ownerId,
            'createdAt' => $this->createdAt,
        ];
    }
}
