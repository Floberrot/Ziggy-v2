<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RevokeInvitation;

use App\Identity\Domain\Exception\CannotRevokeAcceptedInvitationException;
use App\Identity\Domain\Exception\InvitationNotFoundException;
use App\Identity\Domain\Exception\OwnerNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RevokeInvitationHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private InvitationRepository $invitationRepository,
    ) {
    }

    public function __invoke(RevokeInvitationCommand $command): void
    {
        $owner = $this->userRepository->findByEmail(new Email($command->ownerEmail));

        if (null === $owner) {
            throw new OwnerNotFoundException();
        }

        $invitation = $this->invitationRepository->findById($command->invitationId);

        if (null === $invitation || $invitation->ownerId()->value() !== $owner->id()->value()) {
            throw new InvitationNotFoundException();
        }

        if ($invitation->isAccepted()) {
            throw new CannotRevokeAcceptedInvitationException();
        }

        $this->invitationRepository->remove($command->invitationId);
    }
}
