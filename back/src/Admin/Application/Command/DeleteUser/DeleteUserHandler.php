<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\DeleteUser;

use App\Identity\Domain\Exception\UserNotFoundException;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteUserHandler
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function __invoke(DeleteUserCommand $command): void
    {
        $userId = new UserId($command->userId);
        $user = $this->userRepository->findById($userId);

        if (null === $user) {
            throw new UserNotFoundException($command->userId);
        }

        $this->userRepository->remove($userId);
    }
}
