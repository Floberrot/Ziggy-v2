<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\UpdatePetSitter;

use App\Identity\Domain\Exception\OwnerNotFoundException;
use App\Identity\Domain\Exception\PetSitterNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\PetSitterId;
use App\Identity\Domain\Model\PetSitterType;
use App\Identity\Domain\Repository\PetSitterRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdatePetSitterHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PetSitterRepository $petSitterRepository,
    ) {
    }

    public function __invoke(UpdatePetSitterCommand $command): void
    {
        $owner = $this->userRepository->findByEmail(new Email($command->ownerEmail));

        if (null === $owner) {
            throw new OwnerNotFoundException();
        }

        $petSitter = $this->petSitterRepository->findById(new PetSitterId($command->petSitterId));

        if (null === $petSitter) {
            throw new PetSitterNotFoundException();
        }

        if (!$petSitter->ownerId()->equals($owner->id())) {
            throw new PetSitterNotFoundException();
        }

        $petSitter->updateData(
            PetSitterType::from($command->type),
            $command->age,
            $command->phoneNumber,
        );

        $this->petSitterRepository->save($petSitter);
    }
}
