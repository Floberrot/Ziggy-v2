<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\UnscheduleChipType;

use App\Calendar\Domain\Exception\CalendarNotFoundException;
use App\Calendar\Domain\Exception\ChipTypeNotScheduledException;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UnscheduleChipTypeHandler
{
    public function __construct(private CalendarRepository $calendarRepository)
    {
    }

    public function __invoke(UnscheduleChipTypeCommand $command): void
    {
        $calendar = $this->calendarRepository->findByCatId($command->catId);

        if (null === $calendar) {
            throw new CalendarNotFoundException($command->catId);
        }

        if (!in_array($command->chipTypeId, $calendar->scheduledChipTypeIds(), true)) {
            throw new ChipTypeNotScheduledException($command->chipTypeId);
        }

        $calendar->unscheduleChipType($command->chipTypeId);

        $this->calendarRepository->save($calendar);
    }
}
