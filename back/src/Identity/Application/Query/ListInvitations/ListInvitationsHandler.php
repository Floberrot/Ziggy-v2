<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\ListInvitations;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListInvitationsHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private InvitationRepository $invitationRepository,
    ) {
    }

    /** @return list<InvitationView> */
    public function __invoke(ListInvitationsQuery $query): array
    {
        $owner = $this->userRepository->findByEmail(new Email($query->ownerEmail));

        if (null === $owner) {
            return [];
        }

        $invitations = $this->invitationRepository->findByOwnerId($owner->id()->value());

        return array_map(
            static fn ($inv) => new InvitationView(
                id: $inv->id(),
                inviteeEmail: $inv->inviteeEmail()->value(),
                catId: $inv->catId(),
                token: $inv->token(),
                expiresAt: $inv->expiresAt()->format(\DateTimeInterface::ATOM),
                accepted: $inv->isAccepted(),
                expired: $inv->isExpired(),
            ),
            $invitations,
        );
    }
}
