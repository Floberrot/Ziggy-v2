<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\Calendar\Domain\Model\Calendar;
use App\Calendar\Domain\Model\CalendarId;
use App\Calendar\Domain\Model\ChipId;
use App\Calendar\Domain\Repository\CalendarRepository;

final class InMemoryCalendarRepository implements CalendarRepository
{
    /** @var array<string, Calendar> */
    private array $store = [];

    public function save(Calendar $calendar): void
    {
        $this->store[$calendar->id()->value()] = $calendar;
    }

    public function findById(CalendarId $id): ?Calendar
    {
        return $this->store[$id->value()] ?? null;
    }

    public function findByCatId(string $catId): ?Calendar
    {
        foreach ($this->store as $calendar) {
            if ($calendar->catId() === $catId) {
                return $calendar;
            }
        }

        return null;
    }

    public function removeChip(string $chipId): void
    {
        foreach ($this->store as $calendar) {
            $calendar->removeChip(new ChipId($chipId));
        }
    }
}
