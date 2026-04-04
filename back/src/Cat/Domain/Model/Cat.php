<?php

declare(strict_types=1);

namespace App\Cat\Domain\Model;

use App\Cat\Domain\Event\CatAdded;
use App\Cat\Domain\Event\CatUpdated;
use App\Shared\Domain\AggregateRoot;

final class Cat extends AggregateRoot
{
    /** @param list<string> $colors */
    private function __construct(
        private readonly CatId $id,
        private CatName $name,
        private ?float $weight,
        private ?string $breed,
        private array $colors,
        private readonly string $ownerId,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    /** @param list<string> $colors */
    public static function add(
        CatId $id,
        CatName $name,
        string $ownerId,
        ?float $weight = null,
        ?string $breed = null,
        array $colors = [],
    ): self {
        $cat = new self(
            id: $id,
            name: $name,
            weight: $weight,
            breed: $breed,
            colors: $colors,
            ownerId: $ownerId,
            createdAt: new \DateTimeImmutable(),
        );

        $cat->recordEvent(new CatAdded(
            catId: $id->value(),
            name: $name->value(),
            ownerId: $ownerId,
            occurredAt: $cat->createdAt,
        ));

        return $cat;
    }

    public function id(): CatId
    {
        return $this->id;
    }

    public function name(): CatName
    {
        return $this->name;
    }

    public function weight(): ?float
    {
        return $this->weight;
    }

    public function breed(): ?string
    {
        return $this->breed;
    }

    /** @return list<string> */
    public function colors(): array
    {
        return $this->colors;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @param list<string> $colors */
    public function update(CatName $name, ?float $weight, ?string $breed, array $colors): void
    {
        $this->name = $name;
        $this->weight = $weight;
        $this->breed = $breed;
        $this->colors = $colors;

        $this->recordEvent(new CatUpdated(
            catId: $this->id->value(),
            name: $name->value(),
            weight: $weight,
            ownerId: $this->ownerId,
            occurredAt: new \DateTimeImmutable(),
        ));
    }
}
