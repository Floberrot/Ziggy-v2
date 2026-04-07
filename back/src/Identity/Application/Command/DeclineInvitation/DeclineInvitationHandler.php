<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\DeclineInvitation;

use App\Identity\Domain\Exception\InvitationNotFoundException;
use App\Identity\Domain\Repository\InvitationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeclineInvitationHandler
{
    public function __construct(
        private InvitationRepository $invitationRepository,
    ) {
    }

    public function __invoke(DeclineInvitationCommand $command): void
    {
        $invitation = $this->invitationRepository->findByToken($command->token);

        if (null === $invitation) {
            throw new InvitationNotFoundException($command->token);
        }

        $invitation->decline();

        $this->invitationRepository->save($invitation);
    }
}
