<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListPetSitters;

use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\PetSitterRepository;
use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Application\DTO\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListPetSittersAdminHandler
{
    public function __construct(
        private PetSitterRepository $petSitterRepository,
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(ListPetSittersAdminQuery $query): PaginatedResult
    {
        $petSitters = $this->petSitterRepository->findAllPaginated($query->page, $query->limit);
        $total = $this->petSitterRepository->countAll();

        // Batch-load owners by unique UUIDs to avoid N+1
        $ownerIds = array_unique(array_map(static fn ($ps) => $ps->ownerId()->value(), $petSitters));
        $ownersByUuid = [];
        foreach ($ownerIds as $uuid) {
            $owner = $this->userRepository->findById(new UserId($uuid));
            if (null !== $owner) {
                $ownersByUuid[$uuid] = $owner;
            }
        }

        $items = array_map(static function ($ps) use ($ownersByUuid) {
            $owner = $ownersByUuid[$ps->ownerId()->value()] ?? null;

            return (new PetSitterAdminView(
                id: $ps->id()->value(),
                ownerId: $ps->ownerId()->value(),
                ownerEmail: $owner?->email()->value(),
                ownerUsername: $owner?->username(),
                inviteeEmail: $ps->inviteeEmail()->value(),
                userId: $ps->userId()?->value(),
                type: $ps->type()->value,
                age: $ps->age(),
                phoneNumber: $ps->phoneNumber(),
                createdAt: $ps->createdAt()->format(\DateTimeInterface::ATOM),
            ))->toArray();
        }, $petSitters);

        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
