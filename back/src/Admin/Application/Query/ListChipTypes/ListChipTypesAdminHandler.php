<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListChipTypes;

use App\ChipType\Domain\Repository\ChipTypeRepository;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Application\DTO\PaginatedResult;
use DateTimeInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListChipTypesAdminHandler
{
    public function __construct(
        private ChipTypeRepository $chipTypeRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(ListChipTypesAdminQuery $query): PaginatedResult
    {
        $chipTypes = $this->chipTypeRepository->findAllPaginated($query->page, $query->limit);
        $total = $this->chipTypeRepository->countAll();

        $items = array_map(function ($ct) {
            $owner = $this->userRepository->findById(new UserId($ct->ownerId()));

            return new ChipTypeAdminView(
                id: $ct->id()->value(),
                name: $ct->name(),
                color: $ct->color()->value(),
                ownerId: $ct->ownerId(),
                ownerEmail: $owner?->email()->value(),
                ownerUsername: $owner?->username(),
                createdAt: $ct->createdAt()->format(DateTimeInterface::ATOM),
            )->toArray();
        }, $chipTypes);

        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
