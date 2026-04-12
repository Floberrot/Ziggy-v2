<?php

declare(strict_types=1);

namespace App\Tests\Integration\Calendar\Application\Command;

use App\Calendar\Application\Command\ScheduleChipType\ScheduleChipTypeCommand;
use App\Calendar\Application\Command\ScheduleChipType\ScheduleChipTypeHandler;
use App\Calendar\Domain\Exception\ChipTypeAlreadyScheduledException;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ScheduleChipTypeHandlerTest extends KernelTestCase
{
    private ScheduleChipTypeHandler $handler;
    private CalendarRepository $calendarRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(ScheduleChipTypeHandler::class);
        $this->calendarRepository = $container->get(CalendarRepository::class);
    }

    public function testCreatesCalendarAndSchedulesChipType(): void
    {
        $catId = 'cat-schedule-' . uniqid();

        ($this->handler)(new ScheduleChipTypeCommand(
            catId: $catId,
            chipTypeId: 'chiptype-scheduled',
        ));

        $calendar = $this->calendarRepository->findByCatId($catId);

        self::assertNotNull($calendar);
        self::assertContains('chiptype-scheduled', $calendar->scheduledChipTypeIds());
    }

    public function testSchedulesChipTypeOnExistingCalendar(): void
    {
        $catId = 'cat-schedule-existing-' . uniqid();

        ($this->handler)(new ScheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-first'));
        ($this->handler)(new ScheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-second'));

        $calendar = $this->calendarRepository->findByCatId($catId);

        self::assertNotNull($calendar);
        self::assertContains('type-first', $calendar->scheduledChipTypeIds());
        self::assertContains('type-second', $calendar->scheduledChipTypeIds());
    }

    public function testThrowsWhenChipTypeAlreadyScheduled(): void
    {
        $catId = 'cat-schedule-duplicate-' . uniqid();

        ($this->handler)(new ScheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-dup'));

        $this->expectException(ChipTypeAlreadyScheduledException::class);

        ($this->handler)(new ScheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-dup'));
    }
}
