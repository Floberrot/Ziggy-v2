<?php

declare(strict_types=1);

namespace App\Cat\Domain\Event;

use App\Shared\Domain\DomainEvent;

final readonly class CatAdded implements DomainEvent
{
    public function __construct(
        public readonly string $catId,
        public readonly string $name,
        public readonly string $ownerId,
        private \DateTimeImmutable $occurredAt,
    ) {
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
