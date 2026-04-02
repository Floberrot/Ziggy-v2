<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RemovePetSitter;

use App\Identity\Domain\Exception\OwnerNotFoundException;
use App\Identity\Domain\Exception\PetSitterNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\PetSitterId;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\PetSitterRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RemovePetSitterHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PetSitterRepository $petSitterRepository,
        private InvitationRepository $invitationRepository,
    ) {
    }

    public function __invoke(RemovePetSitterCommand $command): void
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

        $pendingInvitations = $this->invitationRepository->findPendingByOwnerAndEmail(
            $owner->id()->value(),
            $petSitter->inviteeEmail()->value(),
        );

        foreach ($pendingInvitations as $invitation) {
            $this->invitationRepository->remove($invitation->id());
        }

        $this->petSitterRepository->remove($petSitter->id());
    }
}
