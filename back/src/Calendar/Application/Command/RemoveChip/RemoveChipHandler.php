<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\RemoveChip;

use App\Calendar\Domain\Exception\CalendarNotFoundException;
use App\Calendar\Domain\Exception\ChipNotFoundException;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RemoveChipHandler
{
    public function __construct(private CalendarRepository $calendarRepository)
    {
    }

    public function __invoke(RemoveChipCommand $command): void
    {
        $calendar = $this->calendarRepository->findByCatId($command->catId);

        if (null === $calendar) {
            throw new CalendarNotFoundException($command->catId);
        }

        $found = false;
        foreach ($calendar->chips() as $chip) {
            if ($chip->id()->value() === $command->chipId) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            throw new ChipNotFoundException($command->chipId);
        }

        $this->calendarRepository->removeChip($command->chipId);
    }
}
