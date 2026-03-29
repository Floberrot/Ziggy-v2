<?php

declare(strict_types=1);

namespace App\Calendar\Application\Query\GetCalendar;

final readonly class CalendarView
{
    /** @param list<ChipView> $chips */
    public function __construct(
        public string $id,
        public string $catId,
        public array $chips,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'catId' => $this->catId,
            'chips' => array_map(static fn (ChipView $chip) => $chip->toArray(), $this->chips),
        ];
    }
}
