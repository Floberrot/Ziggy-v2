<?php

declare(strict_types=1);

namespace App\Cat\Domain\Event;

use App\Shared\Domain\DomainEvent;

final readonly class CatUpdated implements DomainEvent
{
    public function __construct(
        public string $catId,
        public string $name,
        public ?float $weight,
        public string $ownerId,
        private \DateTimeImmutable $occurredAt,
    ) {
    }

    public function occurredAt(): \DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
