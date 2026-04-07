<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\UpdateUser;

use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateUserAdminHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(UpdateUserAdminCommand $command): void
    {
        $user = $this->userRepository->findById(new UserId($command->userId));

        if (null === $user) {
            throw new UserNotFoundException($command->userId);
        }

        if (null !== $command->username) {
            $user->updateUsername($command->username);
        }

        $this->userRepository->save($user);
    }
}
