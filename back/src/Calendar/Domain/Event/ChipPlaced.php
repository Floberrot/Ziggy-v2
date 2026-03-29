<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Event;

use App\Shared\Domain\DomainEvent;

final readonly class ChipPlaced implements DomainEvent
{
    public function __construct(
        public readonly string $chipId,
        public readonly string $calendarId,
        public readonly string $catId,
        public readonly string $chipTypeId,
        public readonly string $date,
        private \DateTimeImmutable $occurredAt,
    ) {
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
