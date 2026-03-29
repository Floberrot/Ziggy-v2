<?php

declare(strict_types=1);

namespace App\Calendar\Application\Query\GetCalendar;

final readonly class GetCalendarQuery
{
    public function __construct(
        public readonly string $catId,
    ) {
    }
}
