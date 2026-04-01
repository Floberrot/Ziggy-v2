<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterOwner;

use App\Identity\Domain\Exception\EmailAlreadyRegisteredException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use App\Shared\Domain\EventBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RegisterOwnerHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private EventBus $eventBus,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function __invoke(RegisterOwnerCommand $command): void
    {
        $email = new Email($command->email);

        if (null !== $this->userRepository->findByEmail($email)) {
            throw new EmailAlreadyRegisteredException($command->email);
        }

        $userId = UserId::generate();

        $hashedPassword = $this->passwordHasher->hashPassword(
            new InMemoryUser($command->email, ''),
            $command->plainPassword,
        );

        $user = User::register(
            id: $userId,
            email: $email,
            hashedPassword: $hashedPassword,
            role: Role::OWNER,
            username: $command->username,
        );

        $this->userRepository->save($user);
        $this->eventBus->publish(...$user->pullDomainEvents());
    }
}
