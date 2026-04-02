<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\ListPetSitters;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Invitation;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\PetSitterRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'query.bus')]
final readonly class ListPetSittersHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PetSitterRepository $petSitterRepository,
        private InvitationRepository $invitationRepository,
    ) {
    }

    /** @return list<PetSitterView> */
    public function __invoke(ListPetSittersQuery $query): array
    {
        $owner = $this->userRepository->findByEmail(new Email($query->ownerEmail));

        if (null === $owner) {
            return [];
        }

        $petSitters = $this->petSitterRepository->findByOwnerId($owner->id()->value());
        $invitations = $this->invitationRepository->findByOwnerId($owner->id()->value());

        $invitationsByEmail = [];
        foreach ($invitations as $invitation) {
            $email = $invitation->inviteeEmail()->value();
            $invitationsByEmail[$email][] = $invitation;
        }

        return array_map(
            static function ($ps) use ($invitationsByEmail): PetSitterView {
                $email = $ps->inviteeEmail()->value();
                $invList = $invitationsByEmail[$email] ?? [];

                $invViews = array_map(
                    static fn (Invitation $inv) => new PetSitterInvitationView(
                        id: $inv->id(),
                        catId: $inv->catId(),
                        token: $inv->token(),
                        accepted: $inv->isAccepted(),
                        declined: $inv->isDeclined(),
                        expired: $inv->isExpired(),
                    ),
                    $invList,
                );

                return new PetSitterView(
                    id: $ps->id()->value(),
                    inviteeEmail: $email,
                    userId: $ps->userId()?->value(),
                    type: $ps->type()->value,
                    age: $ps->age(),
                    phoneNumber: $ps->phoneNumber(),
                    invitations: $invViews,
                );
            },
            $petSitters,
        );
    }
}
