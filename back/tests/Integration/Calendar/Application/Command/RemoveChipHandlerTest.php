<?php

declare(strict_types=1);

namespace App\Tests\Integration\Calendar\Application\Command;

use App\Calendar\Application\Command\PlaceChip\PlaceChipCommand;
use App\Calendar\Application\Command\PlaceChip\PlaceChipHandler;
use App\Calendar\Application\Command\RemoveChip\RemoveChipCommand;
use App\Calendar\Application\Command\RemoveChip\RemoveChipHandler;
use App\Calendar\Domain\Exception\CalendarNotFoundException;
use App\Calendar\Domain\Exception\ChipNotFoundException;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class RemoveChipHandlerTest extends KernelTestCase
{
    private PlaceChipHandler $placeHandler;
    private RemoveChipHandler $removeHandler;
    private CalendarRepository $calendarRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->placeHandler = $container->get(PlaceChipHandler::class);
        $this->removeHandler = $container->get(RemoveChipHandler::class);
        $this->calendarRepository = $container->get(CalendarRepository::class);
    }

    public function testRemovesChipSuccessfully(): void
    {
        $catId = 'cat-remove-' . uniqid();

        ($this->placeHandler)(new PlaceChipCommand(
            catId: $catId,
            chipTypeId: 'type-1',
            dateTime: '2026-03-01',
            authorId: 'author-xyz',
        ));

        $calendar = $this->calendarRepository->findByCatId($catId);
        self::assertNotNull($calendar);
        $chipId = $calendar->chips()[0]->id()->value();

        ($this->removeHandler)(new RemoveChipCommand(
            catId: $catId,
            chipId: $chipId,
        ));

        $updated = $this->calendarRepository->findByCatId($catId);
        self::assertNotNull($updated);
        self::assertCount(0, $updated->chips());
    }

    public function testThrowsWhenCalendarNotFound(): void
    {
        $this->expectException(CalendarNotFoundException::class);

        ($this->removeHandler)(new RemoveChipCommand(
            catId: 'nonexistent-cat',
            chipId: 'nonexistent-chip',
        ));
    }

    public function testThrowsWhenChipNotFound(): void
    {
        $catId = 'cat-remove-chip-' . uniqid();

        ($this->placeHandler)(new PlaceChipCommand(
            catId: $catId,
            chipTypeId: 'type-1',
            dateTime: '2026-03-01',
            authorId: 'author-xyz',
        ));

        $this->expectException(ChipNotFoundException::class);

        ($this->removeHandler)(new RemoveChipCommand(
            catId: $catId,
            chipId: 'nonexistent-chip-id',
        ));
    }
}
