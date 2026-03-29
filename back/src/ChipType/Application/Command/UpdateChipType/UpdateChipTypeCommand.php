<?php

declare(strict_types=1);

namespace App\ChipType\Application\Command\UpdateChipType;

final readonly class UpdateChipTypeCommand
{
    public function __construct(
        public string $chipTypeId,
        public string $requestingUserId,
        public string $name,
        public string $color,
    ) {
    }
}
