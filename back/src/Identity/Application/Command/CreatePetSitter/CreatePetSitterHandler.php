<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\CreatePetSitter;

use App\Identity\Application\Port\InvitationMailer;
use App\Identity\Domain\Exception\OwnerNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Invitation;
use App\Identity\Domain\Model\PetSitter;
use App\Identity\Domain\Model\PetSitterId;
use App\Identity\Domain\Model\PetSitterType;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\PetSitterRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreatePetSitterHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private PetSitterRepository $petSitterRepository,
        private InvitationRepository $invitationRepository,
        private InvitationMailer $invitationMailer,
    ) {
    }

    public function __invoke(CreatePetSitterCommand $command): void
    {
        $owner = $this->userRepository->findByEmail(new Email($command->ownerEmail));

        if (null === $owner) {
            throw new OwnerNotFoundException();
        }

        $petSitter = PetSitter::create(
            id: PetSitterId::generate(),
            ownerId: $owner->id(),
            inviteeEmail: new Email($command->inviteeEmail),
            type: PetSitterType::from($command->type),
            age: $command->age,
            phoneNumber: $command->phoneNumber,
        );

        $invitation = Invitation::create(
            id: Uuid::v7()->toRfc4122(),
            ownerId: $owner->id(),
            inviteeEmail: new Email($command->inviteeEmail),
            catId: $command->catId,
        );

        $this->petSitterRepository->save($petSitter);
        $this->invitationRepository->save($invitation);
        $this->invitationMailer->sendInvitation($command->inviteeEmail, $invitation->token());
    }
}
