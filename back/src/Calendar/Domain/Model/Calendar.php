<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Model;

use App\Calendar\Domain\Event\ChipPlaced;
use App\Shared\Domain\AggregateRoot;

final class Calendar extends AggregateRoot
{
    /** @var list<Chip> */
    private array $chips = [];

    private function __construct(
        private readonly CalendarId $id,
        private readonly string $catId,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function create(
        CalendarId $id,
        string $catId,
    ): self {
        return new self(
            id: $id,
            catId: $catId,
            createdAt: new \DateTimeImmutable(),
        );
    }

    public function placeChip(
        ChipId $chipId,
        string $chipTypeId,
        \DateTimeImmutable $date,
        string $authorId,
        ?string $note = null,
    ): void {
        $chip = new Chip(
            id: $chipId,
            chipTypeId: $chipTypeId,
            date: $date,
            note: $note,
            authorId: $authorId,
        );

        $this->chips[] = $chip;

        $this->recordEvent(new ChipPlaced(
            chipId: $chipId->value(),
            calendarId: $this->id->value(),
            catId: $this->catId,
            chipTypeId: $chipTypeId,
            date: $date->format('Y-m-d'),
            occurredAt: new \DateTimeImmutable(),
        ));
    }

    public function id(): CalendarId
    {
        return $this->id;
    }

    public function catId(): string
    {
        return $this->catId;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return list<Chip> */
    public function chips(): array
    {
        return $this->chips;
    }

    /** @param list<Chip> $chips */
    public function setChips(array $chips): void
    {
        $this->chips = $chips;
    }

    public function removeChip(ChipId $chipId): void
    {
        $this->chips = array_values(
            array_filter(
                $this->chips,
                static fn (Chip $chip) => $chip->id()->value() !== $chipId->value(),
            )
        );
    }
}
