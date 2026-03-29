<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\RemoveChip;

final readonly class RemoveChipCommand
{
    public function __construct(
        public string $catId,
        public string $chipId,
    ) {
    }
}
