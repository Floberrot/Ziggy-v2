<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\ListCats;

use App\Cat\Domain\Repository\CatRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListCatsHandler
{
    public function __construct(
        private CatRepository $catRepository,
    ) {
    }

    /** @return list<CatView> */
    public function __invoke(ListCatsQuery $query): array
    {
        $cats = $this->catRepository->findByOwnerId($query->ownerId);

        return array_map(
            static fn ($cat) => new CatView(
                id: $cat->id()->value(),
                name: $cat->name()->value(),
                weight: $cat->weight(),
                breed: $cat->breed(),
                colors: $cat->colors(),
                ownerId: $cat->ownerId(),
                createdAt: $cat->createdAt()->format(\DateTimeInterface::ATOM),
            ),
            $cats,
        );
    }
}
