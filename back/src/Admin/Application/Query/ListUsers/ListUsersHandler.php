<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListUsers;

use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Application\DTO\PaginatedResult;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListUsersHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(ListUsersQuery $query): PaginatedResult
    {
        $users = $this->userRepository->findAllPaginated($query->page, $query->limit);
        $total = $this->userRepository->countAll();

        $items = array_map(
            static fn ($user) => (new UserAdminView(
                id: $user->id()->value(),
                email: $user->email()->value(),
                role: $user->role()->value,
                username: $user->username(),
                createdAt: $user->createdAt()->format(\DateTimeInterface::ATOM),
            ))->toArray(),
            $users,
        );

        return new PaginatedResult(
            items: $items,
            total: $total,
            page: $query->page,
            limit: $query->limit,
        );
    }
}
