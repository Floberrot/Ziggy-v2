<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\GetCatWeightHistory;

use App\Cat\Domain\Exception\CatNotFoundException;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Repository\CatRepository;
use App\Cat\Domain\Repository\CatWeightRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetCatWeightHistoryHandler
{
    public function __construct(
        private CatRepository $catRepository,
        private CatWeightRepository $catWeightRepository,
    ) {
    }

    /** @return list<WeightEntryView> */
    public function __invoke(GetCatWeightHistoryQuery $query): array
    {
        $cat = $this->catRepository->findById(new CatId($query->catId));

        if (null === $cat || $cat->ownerId() !== $query->requestingUserId) {
            throw new CatNotFoundException($query->catId);
        }

        $entries = $this->catWeightRepository->findByCatId(new CatId($query->catId));

        return array_map(
            static fn ($entry) => new WeightEntryView(
                weight: $entry->weight,
                recordedAt: $entry->recordedAt->format(\DateTimeInterface::ATOM),
            ),
            $entries,
        );
    }
}
