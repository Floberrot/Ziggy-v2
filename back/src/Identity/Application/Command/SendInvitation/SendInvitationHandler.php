<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\SendInvitation;

use App\Identity\Domain\Exception\OwnerNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Invitation;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class SendInvitationHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private InvitationRepository $invitationRepository,
    ) {
    }

    public function __invoke(SendInvitationCommand $command): string
    {
        $owner = $this->userRepository->findByEmail(new Email($command->ownerEmail));

        if (null === $owner) {
            throw new OwnerNotFoundException();
        }

        $invitation = Invitation::create(
            id: Uuid::v7()->toRfc4122(),
            ownerId: $owner->id(),
            inviteeEmail: new Email($command->inviteeEmail),
            catId: $command->catId,
        );

        $this->invitationRepository->save($invitation);

        return $invitation->token();
    }
}
