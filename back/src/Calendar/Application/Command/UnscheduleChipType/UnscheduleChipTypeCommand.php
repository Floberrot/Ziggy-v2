<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\UnscheduleChipType;

final readonly class UnscheduleChipTypeCommand
{
    public function __construct(
        public string $catId,
        public string $chipTypeId,
    ) {
    }
}
