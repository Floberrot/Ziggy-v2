<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\AcceptInvitation;

use App\Identity\Domain\Exception\AccountAlreadyExistsException;
use App\Identity\Domain\Exception\InvitationAlreadyAcceptedException;
use App\Identity\Domain\Exception\InvitationExpiredException;
use App\Identity\Domain\Exception\InvitationNotFoundException;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\PetSitterRepository;
use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Domain\EventBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class AcceptInvitationHandler
{
    public function __construct(
        private InvitationRepository $invitationRepository,
        private UserRepository $userRepository,
        private PetSitterRepository $petSitterRepository,
        private EventBus $eventBus,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(AcceptInvitationCommand $command): void
    {
        $invitation = $this->invitationRepository->findByToken($command->token);

        if (null === $invitation) {
            throw new InvitationNotFoundException();
        }

        if ($invitation->isExpired()) {
            throw new InvitationExpiredException();
        }

        if ($invitation->isAccepted()) {
            throw new InvitationAlreadyAcceptedException();
        }

        if (null !== $this->userRepository->findByEmail($invitation->inviteeEmail())) {
            throw new AccountAlreadyExistsException($invitation->inviteeEmail()->value());
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            new InMemoryUser($invitation->inviteeEmail()->value(), ''),
            $command->plainPassword,
        );

        $user = User::register(
            id: UserId::generate(),
            email: $invitation->inviteeEmail(),
            hashedPassword: $hashedPassword,
            role: Role::PET_SITTER,
            username: $command->username,
        );

        $this->userRepository->save($user);
        $this->invitationRepository->markAccepted($invitation->token());

        $petSitter = $this->petSitterRepository->findByOwnerAndEmail(
            $invitation->ownerId()->value(),
            $invitation->inviteeEmail()->value(),
        );

        if (null !== $petSitter) {
            $petSitter->linkUser($user->id());
            $this->petSitterRepository->save($petSitter);
        }

        $this->eventBus->publish(...$user->pullDomainEvents());
    }
}
