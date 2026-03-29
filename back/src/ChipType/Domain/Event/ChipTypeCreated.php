<?php

declare(strict_types=1);

namespace App\ChipType\Domain\Event;

use App\Shared\Domain\DomainEvent;
use DateTimeImmutable;

final readonly class ChipTypeCreated implements DomainEvent
{
    public function __construct(
        public string $chipTypeId,
        public string $name,
        public string $color,
        public string $ownerId,
        private DateTimeImmutable $occurredAt,
    ) {
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
