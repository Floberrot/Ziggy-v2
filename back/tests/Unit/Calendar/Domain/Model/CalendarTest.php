<?php

declare(strict_types=1);

namespace App\Tests\Unit\Calendar\Domain\Model;

use App\Calendar\Domain\Exception\ChipNotFoundException;
use App\Calendar\Domain\Model\Calendar;
use App\Calendar\Domain\Model\CalendarId;
use App\Calendar\Domain\Model\ChipId;
use App\Calendar\Domain\Event\ChipPlaced;
use PHPUnit\Framework\TestCase;

final class CalendarTest extends TestCase
{
    public function testCreateReturnsCalendarWithNoCHips(): void
    {
        $calendar = Calendar::create(
            id: CalendarId::generate(),
            catId: 'cat-123',
        );

        self::assertSame('cat-123', $calendar->catId());
        self::assertEmpty($calendar->chips());
        self::assertEmpty($calendar->pullDomainEvents());
    }

    public function testPlaceChipAddsChipAndRecordsEvent(): void
    {
        $calendar = Calendar::create(
            id: CalendarId::generate(),
            catId: 'cat-123',
        );

        $chipId = ChipId::generate();
        $date = new \DateTimeImmutable('2026-01-15');

        $calendar->placeChip(
            chipId: $chipId,
            chipTypeId: 'type-abc',
            date: $date,
            authorId: 'author-xyz',
            note: 'Test note',
        );

        self::assertCount(1, $calendar->chips());
        self::assertSame('type-abc', $calendar->chips()[0]->chipTypeId());
        self::assertSame('Test note', $calendar->chips()[0]->note());
        self::assertSame('author-xyz', $calendar->chips()[0]->authorId());

        $events = $calendar->pullDomainEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ChipPlaced::class, $events[0]);
    }

    public function testPlaceMultipleChipsAccumulatesAll(): void
    {
        $calendar = Calendar::create(CalendarId::generate(), 'cat-123');

        $calendar->placeChip(ChipId::generate(), 'type-1', new \DateTimeImmutable(), 'author');
        $calendar->placeChip(ChipId::generate(), 'type-2', new \DateTimeImmutable(), 'author');

        self::assertCount(2, $calendar->chips());
    }

    public function testRemoveChipDeletesItFromList(): void
    {
        $calendar = Calendar::create(CalendarId::generate(), 'cat-123');
        $chipId = ChipId::generate();

        $calendar->placeChip($chipId, 'type-1', new \DateTimeImmutable(), 'author');
        self::assertCount(1, $calendar->chips());

        $calendar->removeChip($chipId);

        self::assertCount(0, $calendar->chips());
    }

    public function testRemoveChipLeavesOtherChipsIntact(): void
    {
        $calendar = Calendar::create(CalendarId::generate(), 'cat-123');
        $chip1 = ChipId::generate();
        $chip2 = ChipId::generate();

        $calendar->placeChip($chip1, 'type-1', new \DateTimeImmutable(), 'author');
        $calendar->placeChip($chip2, 'type-2', new \DateTimeImmutable(), 'author');

        $calendar->removeChip($chip1);

        self::assertCount(1, $calendar->chips());
        self::assertSame($chip2->value(), $calendar->chips()[0]->id()->value());
    }

    public function testPullDomainEventsClearsEvents(): void
    {
        $calendar = Calendar::create(CalendarId::generate(), 'cat-123');
        $calendar->placeChip(ChipId::generate(), 'type-1', new \DateTimeImmutable(), 'author');

        $events = $calendar->pullDomainEvents();
        self::assertCount(1, $events);

        $secondPull = $calendar->pullDomainEvents();
        self::assertEmpty($secondPull);
    }
}
