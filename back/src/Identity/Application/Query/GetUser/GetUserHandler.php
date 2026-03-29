<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetUser;

use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(GetUserQuery $query): ?UserView
    {
        $user = $this->userRepository->findById(new UserId($query->userId));

        if (null === $user) {
            return null;
        }

        return new UserView(
            id: $user->id()->value(),
            email: $user->email()->value(),
            role: $user->role()->value,
            createdAt: $user->createdAt()->format(\DateTimeInterface::ATOM),
        );
    }
}
