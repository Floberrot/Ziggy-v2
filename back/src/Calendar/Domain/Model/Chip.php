<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Model;

final class Chip
{
    public function __construct(
        private readonly ChipId $id,
        private readonly string $chipTypeId,
        private readonly \DateTimeImmutable $date,
        private ?string $note,
        private readonly string $authorId,
    ) {
    }

    public function id(): ChipId
    {
        return $this->id;
    }

    public function chipTypeId(): string
    {
        return $this->chipTypeId;
    }

    public function date(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function note(): ?string
    {
        return $this->note;
    }

    public function authorId(): string
    {
        return $this->authorId;
    }

    public function updateNote(?string $note): void
    {
        $this->note = $note;
    }
}
