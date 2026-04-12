<?php

declare(strict_types=1);

namespace App\Tests\Integration\Calendar\Application\Command;

use App\Calendar\Application\Command\ScheduleChipType\ScheduleChipTypeCommand;
use App\Calendar\Application\Command\ScheduleChipType\ScheduleChipTypeHandler;
use App\Calendar\Application\Command\UnscheduleChipType\UnscheduleChipTypeCommand;
use App\Calendar\Application\Command\UnscheduleChipType\UnscheduleChipTypeHandler;
use App\Calendar\Domain\Exception\CalendarNotFoundException;
use App\Calendar\Domain\Exception\ChipTypeNotScheduledException;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class UnscheduleChipTypeHandlerTest extends KernelTestCase
{
    private UnscheduleChipTypeHandler $handler;
    private ScheduleChipTypeHandler $scheduleHandler;
    private CalendarRepository $calendarRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(UnscheduleChipTypeHandler::class);
        $this->scheduleHandler = $container->get(ScheduleChipTypeHandler::class);
        $this->calendarRepository = $container->get(CalendarRepository::class);
    }

    public function testUnschedulesChipTypeFromCalendar(): void
    {
        $catId = 'cat-unschedule-' . uniqid();

        ($this->scheduleHandler)(new ScheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-remove'));

        ($this->handler)(new UnscheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-remove'));

        $calendar = $this->calendarRepository->findByCatId($catId);

        self::assertNotNull($calendar);
        self::assertNotContains('type-remove', $calendar->scheduledChipTypeIds());
    }

    public function testThrowsWhenCalendarDoesNotExist(): void
    {
        $this->expectException(CalendarNotFoundException::class);

        ($this->handler)(new UnscheduleChipTypeCommand(
            catId: 'cat-nonexistent-' . uniqid(),
            chipTypeId: 'type-any',
        ));
    }

    public function testThrowsWhenChipTypeNotScheduled(): void
    {
        $catId = 'cat-unsched-' . uniqid();

        ($this->scheduleHandler)(new ScheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-other'));

        $this->expectException(ChipTypeNotScheduledException::class);

        ($this->handler)(new UnscheduleChipTypeCommand(catId: $catId, chipTypeId: 'type-not-scheduled'));
    }
}
