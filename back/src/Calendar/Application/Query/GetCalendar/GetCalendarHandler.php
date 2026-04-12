<?php

declare(strict_types=1);

namespace App\Calendar\Application\Query\GetCalendar;

use App\Calendar\Domain\Model\Chip;
use App\Calendar\Domain\Repository\CalendarRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetCalendarHandler
{
    public function __construct(
        private CalendarRepository $calendarRepository,
        private AuthorReadModel $authorReadModel,
    ) {
    }

    public function __invoke(GetCalendarQuery $query): ?CalendarView
    {
        $calendar = $this->calendarRepository->findByCatId($query->catId);

        if (null === $calendar) {
            return null;
        }

        $authorIds = [];
        foreach ($calendar->chips() as $chip) {
            $authorId = $chip->authorId();
            if (!in_array($authorId, $authorIds, true)) {
                $authorIds[] = $authorId;
            }
        }

        $usernameMap = $this->authorReadModel->findUsernamesByIds($authorIds);

        $chipViews = array_map(
            static fn (Chip $chip): ChipView => new ChipView(
                id: $chip->id()->value(),
                chipTypeId: $chip->chipTypeId(),
                date: $chip->date()->format(\DateTimeInterface::ATOM),
                note: $chip->note(),
                authorUsername: $usernameMap[$chip->authorId()] ?? $chip->authorId(),
            ),
            $calendar->chips(),
        );

        return new CalendarView(
            id: $calendar->id()->value(),
            catId: $calendar->catId(),
            chips: $chipViews,
            scheduledChipTypeIds: $calendar->scheduledChipTypeIds(),
        );
    }
}
