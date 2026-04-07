<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListCats;

use App\Cat\Domain\Repository\CatRepository;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Application\DTO\PaginatedResult;
use DateTimeInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListCatsAdminHandler
{
    public function __construct(
        private CatRepository $catRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(ListCatsAdminQuery $query): PaginatedResult
    {
        $cats = $this->catRepository->findAllPaginated($query->page, $query->limit);
        $total = $this->catRepository->countAll();

        $items = array_map(function ($cat) {
            $owner = $this->userRepository->findById(new UserId($cat->ownerId()));

            return new CatAdminView(
                id: $cat->id()->value(),
                name: $cat->name()->value(),
                weight: $cat->weight(),
                breed: $cat->breed(),
                colors: $cat->colors(),
                ownerId: $cat->ownerId(),
                ownerEmail: $owner?->email()->value(),
                ownerUsername: $owner?->username(),
                createdAt: $cat->createdAt()->format(DateTimeInterface::ATOM),
            )->toArray();
        }, $cats);

        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
