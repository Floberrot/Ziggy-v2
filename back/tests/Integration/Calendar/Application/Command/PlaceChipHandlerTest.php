<?php

declare(strict_types=1);

namespace App\Tests\Integration\Calendar\Application\Command;

use App\Calendar\Application\Command\PlaceChip\PlaceChipCommand;
use App\Calendar\Application\Command\PlaceChip\PlaceChipHandler;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PlaceChipHandlerTest extends KernelTestCase
{
    private PlaceChipHandler $handler;
    private CalendarRepository $calendarRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(PlaceChipHandler::class);
        $this->calendarRepository = $container->get(CalendarRepository::class);
    }

    public function testCreatesCalendarWhenNoneExistsAndPlacesChip(): void
    {
        $catId = 'cat-integration-' . uniqid();

        ($this->handler)(new PlaceChipCommand(
            catId: $catId,
            chipTypeId: 'chiptype-abc',
            date: '2026-03-01',
            authorId: 'author-xyz',
            note: 'Integration test chip',
        ));

        $calendar = $this->calendarRepository->findByCatId($catId);

        self::assertNotNull($calendar);
        self::assertCount(1, $calendar->chips());
        self::assertSame('chiptype-abc', $calendar->chips()[0]->chipTypeId());
        self::assertSame('Integration test chip', $calendar->chips()[0]->note());
    }

    public function testAddsChipToExistingCalendar(): void
    {
        $catId = 'cat-integration-' . uniqid();

        ($this->handler)(new PlaceChipCommand(
            catId: $catId,
            chipTypeId: 'chiptype-1',
            date: '2026-03-01',
            authorId: 'author-xyz',
        ));

        ($this->handler)(new PlaceChipCommand(
            catId: $catId,
            chipTypeId: 'chiptype-2',
            date: '2026-03-02',
            authorId: 'author-xyz',
        ));

        $calendar = $this->calendarRepository->findByCatId($catId);

        self::assertNotNull($calendar);
        self::assertCount(2, $calendar->chips());
    }
}
