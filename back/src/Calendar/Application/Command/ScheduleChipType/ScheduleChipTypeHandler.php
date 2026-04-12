<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\ScheduleChipType;

use App\Calendar\Domain\Exception\ChipTypeAlreadyScheduledException;
use App\Calendar\Domain\Model\Calendar;
use App\Calendar\Domain\Model\CalendarId;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class ScheduleChipTypeHandler
{
    public function __construct(private CalendarRepository $calendarRepository)
    {
    }

    public function __invoke(ScheduleChipTypeCommand $command): void
    {
        $calendar = $this->calendarRepository->findByCatId($command->catId);

        if (null === $calendar) {
            $calendar = Calendar::create(
                id: CalendarId::generate(),
                catId: $command->catId,
            );
        }

        if (in_array($command->chipTypeId, $calendar->scheduledChipTypeIds(), true)) {
            throw new ChipTypeAlreadyScheduledException($command->chipTypeId);
        }

        $calendar->scheduleChipType($command->chipTypeId);

        $this->calendarRepository->save($calendar);
    }
}
