<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetOwnerProfile;

use App\Identity\Application\Port\OwnerStatsPort;
use App\Identity\Domain\Exception\OwnerNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Repository\OwnerProfileRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class GetOwnerProfileHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private OwnerProfileRepository $ownerProfileRepository,
        private OwnerStatsPort $ownerStatsPort,
    ) {
    }

    public function __invoke(GetOwnerProfileQuery $query): OwnerProfileView
    {
        $user = $this->userRepository->findByEmail(new Email($query->ownerEmail));

        if (null === $user) {
            throw new OwnerNotFoundException();
        }

        $profile = $this->ownerProfileRepository->findByUserId($user->id());

        return new OwnerProfileView(
            userId: $user->id()->value(),
            email: $user->email()->value(),
            username: $user->username(),
            age: $profile?->age(),
            phoneNumber: $profile?->phoneNumber(),
            catsCount: $this->ownerStatsPort->countCatsByOwnerId($user->id()->value()),
            chipsCount: $this->ownerStatsPort->countChipsByOwnerId($user->id()->value()),
        );
    }
}
