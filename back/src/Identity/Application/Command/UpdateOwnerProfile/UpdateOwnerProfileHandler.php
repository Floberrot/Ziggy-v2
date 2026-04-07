<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\UpdateOwnerProfile;

use App\Identity\Domain\Exception\OwnerNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\OwnerProfile;
use App\Identity\Domain\Repository\OwnerProfileRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateOwnerProfileHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private OwnerProfileRepository $ownerProfileRepository,
    ) {
    }

    public function __invoke(UpdateOwnerProfileCommand $command): void
    {
        $user = $this->userRepository->findByEmail(new Email($command->ownerEmail));

        if (null === $user) {
            throw new OwnerNotFoundException($command->ownerEmail);
        }

        $profile = $this->ownerProfileRepository->findByUserId($user->id())
            ?? OwnerProfile::create($user->id());

        $profile->update($command->age, $command->phoneNumber);

        $this->ownerProfileRepository->save($profile);
    }
}
