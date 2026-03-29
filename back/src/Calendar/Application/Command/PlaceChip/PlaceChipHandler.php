<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\PlaceChip;

use App\Calendar\Domain\Model\Calendar;
use App\Calendar\Domain\Model\CalendarId;
use App\Calendar\Domain\Model\ChipId;
use App\Calendar\Domain\Repository\CalendarRepository;
use App\Shared\Domain\EventBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class PlaceChipHandler
{
    public function __construct(
        private CalendarRepository $calendarRepository,
        private EventBus $eventBus,
    ) {
    }

    public function __invoke(PlaceChipCommand $command): void
    {
        $calendar = $this->calendarRepository->findByCatId($command->catId);

        if (null === $calendar) {
            $calendar = Calendar::create(
                id: CalendarId::generate(),
                catId: $command->catId,
            );
        }

        $now = new \DateTimeImmutable();
        $date = new \DateTimeImmutable($command->date . ' ' . $now->format('H:i:s'));

        $calendar->placeChip(
            chipId: ChipId::generate(),
            chipTypeId: $command->chipTypeId,
            date: $date,
            note: $command->note,
            authorId: $command->authorId,
        );

        $this->calendarRepository->save($calendar);
        $this->eventBus->publish(...$calendar->pullDomainEvents());
    }
}
