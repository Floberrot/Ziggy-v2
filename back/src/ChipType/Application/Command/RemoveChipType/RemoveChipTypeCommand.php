<?php

declare(strict_types=1);

namespace App\ChipType\Application\Command\RemoveChipType;

final readonly class RemoveChipTypeCommand
{
    public function __construct(
        public string $chipTypeId,
        public string $requestingUserId,
    ) {
    }
}
