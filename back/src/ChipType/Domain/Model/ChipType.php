<?php

declare(strict_types=1);

namespace App\ChipType\Domain\Model;

use App\ChipType\Domain\Event\ChipTypeCreated;
use App\Shared\Domain\AggregateRoot;
use DateTimeImmutable;

final class ChipType extends AggregateRoot
{
    private function __construct(
        private readonly ChipTypeId $id,
        private string $name,
        private ChipColor $color,
        private readonly string $ownerId,
        private readonly DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        ChipTypeId $id,
        string $name,
        ChipColor $color,
        string $ownerId,
    ): self {
        $chipType = new self(
            id: $id,
            name: $name,
            color: $color,
            ownerId: $ownerId,
            createdAt: new DateTimeImmutable(),
        );

        $chipType->recordEvent(new ChipTypeCreated(
            chipTypeId: $id->value(),
            name: $name,
            color: $color->value(),
            ownerId: $ownerId,
            occurredAt: $chipType->createdAt,
        ));

        return $chipType;
    }

    public function id(): ChipTypeId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function color(): ChipColor
    {
        return $this->color;
    }

    public function ownerId(): string
    {
        return $this->ownerId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function rename(string $name): void
    {
        $this->name = $name;
    }

    public function changeColor(ChipColor $color): void
    {
        $this->color = $color;
    }
}
