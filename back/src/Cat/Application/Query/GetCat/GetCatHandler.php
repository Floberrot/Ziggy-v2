<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\GetCat;

use App\Cat\Application\Query\ListCats\CatView;
use App\Cat\Domain\Exception\CatNotFoundException;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Repository\CatRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetCatHandler
{
    public function __construct(private CatRepository $catRepository)
    {
    }

    public function __invoke(GetCatQuery $query): CatView
    {
        $cat = $this->catRepository->findById(new CatId($query->catId));

        if (null === $cat || $cat->ownerId() !== $query->requestingUserId) {
            throw new CatNotFoundException($query->catId);
        }

        return new CatView(
            id: $cat->id()->value(),
            name: $cat->name()->value(),
            weight: $cat->weight(),
            breed: $cat->breed(),
            colors: $cat->colors(),
            ownerId: $cat->ownerId(),
            createdAt: $cat->createdAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
