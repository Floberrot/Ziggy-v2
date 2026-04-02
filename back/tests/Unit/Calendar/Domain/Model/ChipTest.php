<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calendar\Domain\Model;

use App\Calendar\Domain\Model\Chip;
use App\Calendar\Domain\Model\ChipId;
use PHPUnit\Framework\TestCase;

final class ChipTest extends TestCase
{
    public function testChipHoldsGivenProperties(): void
    {
        $chipId = ChipId::generate();
        $date = new \DateTimeImmutable('2026-03-01');

        $chip = new Chip(
            id: $chipId,
            chipTypeId: 'type-abc',
            date: $date,
            note: 'Some note',
            authorId: 'author-1',
        );

        self::assertSame($chipId->value(), $chip->id()->value());
        self::assertSame('type-abc', $chip->chipTypeId());
        self::assertSame($date, $chip->date());
        self::assertSame('Some note', $chip->note());
        self::assertSame('author-1', $chip->authorId());
    }

    public function testChipCanHaveNullNote(): void
    {
        $chip = new Chip(
            id: ChipId::generate(),
            chipTypeId: 'type-abc',
            date: new \DateTimeImmutable(),
            note: null,
            authorId: 'author-1',
        );

        self::assertNull($chip->note());
    }

    public function testUpdateNoteMutatesNote(): void
    {
        $chip = new Chip(
            id: ChipId::generate(),
            chipTypeId: 'type-abc',
            date: new \DateTimeImmutable(),
            note: 'Original',
            authorId: 'author-1',
        );

        $chip->updateNote('Updated note');

        self::assertSame('Updated note', $chip->note());
    }

    public function testUpdateNoteCanSetNull(): void
    {
        $chip = new Chip(
            id: ChipId::generate(),
            chipTypeId: 'type-abc',
            date: new \DateTimeImmutable(),
            note: 'Has note',
            authorId: 'author-1',
        );

        $chip->updateNote(null);

        self::assertNull($chip->note());
    }
}
