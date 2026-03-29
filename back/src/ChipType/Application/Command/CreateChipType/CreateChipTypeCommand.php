<?php

declare(strict_types=1);

namespace App\ChipType\Application\Command\CreateChipType;

final readonly class CreateChipTypeCommand
{
    public function __construct(
        public readonly string $ownerId,
        public readonly string $name,
        public readonly string $color,
    ) {
    }
}
