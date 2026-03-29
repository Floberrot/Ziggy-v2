<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Repository;

use App\Calendar\Domain\Model\Calendar;
use App\Calendar\Domain\Model\CalendarId;

interface CalendarRepository
{
    public function save(Calendar $calendar): void;

    public function findById(CalendarId $id): ?Calendar;

    public function findByCatId(string $catId): ?Calendar;

    public function removeChip(string $chipId): void;
}
