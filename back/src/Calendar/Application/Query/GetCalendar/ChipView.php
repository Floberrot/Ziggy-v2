<?php

declare(strict_types=1);

namespace App\Calendar\Application\Query\GetCalendar;

final readonly class ChipView
{
    public function __construct(
        public string $id,
        public string $chipTypeId,
        public string $date,
        public ?string $note,
        public string $authorUsername,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'chipTypeId' => $this->chipTypeId,
            'date' => $this->date,
            'note' => $this->note,
            'authorUsername' => $this->authorUsername,
        ];
    }
}
