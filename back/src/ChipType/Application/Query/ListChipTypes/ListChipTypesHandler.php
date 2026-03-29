<?php

declare(strict_types=1);

namespace App\ChipType\Application\Query\ListChipTypes;

use App\ChipType\Domain\Repository\ChipTypeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListChipTypesHandler
{
    public function __construct(
        private ChipTypeRepository $chipTypeRepository,
    ) {
    }

    /** @return list<ChipTypeView> */
    public function __invoke(ListChipTypesQuery $query): array
    {
        $chipTypes = $this->chipTypeRepository->findByOwnerId($query->ownerId);

        return array_map(
            static fn ($ct) => new ChipTypeView(
                id: $ct->id()->value(),
                name: $ct->name(),
                color: $ct->color()->value(),
                ownerId: $ct->ownerId(),
                createdAt: $ct->createdAt()->format(\DateTimeInterface::ATOM),
            ),
            $chipTypes,
        );
    }
}
