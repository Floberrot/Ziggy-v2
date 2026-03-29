<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\UpdateUsername;

use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateUsernameHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(UpdateUsernameCommand $command): void
    {
        $user = $this->userRepository->findById(new UserId($command->userId));

        if (null === $user) {
            throw new \DomainException('User not found.');
        }

        $user->updateUsername($command->username);
        $this->userRepository->save($user);
    }
}
