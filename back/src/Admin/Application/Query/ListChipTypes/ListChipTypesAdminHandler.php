<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListChipTypes;

use App\ChipType\Domain\Repository\ChipTypeRepository;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Application\DTO\PaginatedResult;
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

        $ownerEmails = array_unique(array_map(static fn ($ct) => $ct->ownerId(), $chipTypes));
        $ownersByEmail = [];
        foreach ($ownerEmails as $email) {
            $owner = $this->userRepository->findByEmail(new Email($email));
            if (null !== $owner) {
                $ownersByEmail[$email] = $owner;
            }
        }

        $items = array_map(static function ($ct) use ($ownersByEmail) {
            $owner = $ownersByEmail[$ct->ownerId()] ?? null;

            return (new ChipTypeAdminView(
                id: $ct->id()->value(),
                name: $ct->name(),
                color: $ct->color()->value(),
                ownerId: $ct->ownerId(),
                ownerEmail: $owner?->email()->value(),
                ownerUsername: $owner?->username(),
                createdAt: $ct->createdAt()->format(\DateTimeInterface::ATOM),
            ))->toArray();
        }, $chipTypes);

        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
