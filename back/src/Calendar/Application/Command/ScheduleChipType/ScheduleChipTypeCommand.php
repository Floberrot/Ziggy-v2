<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\ScheduleChipType;

final readonly class ScheduleChipTypeCommand
{
    public function __construct(
        public string $catId,
        public string $chipTypeId,
    ) {
    }
}
