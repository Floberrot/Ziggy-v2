<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\DeletePetSitter;

use App\Identity\Domain\Exception\PetSitterNotFoundException;
use App\Identity\Domain\Model\PetSitterId;
use App\Identity\Domain\Repository\PetSitterRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeletePetSitterAdminHandler
{
    public function __construct(private PetSitterRepository $petSitterRepository)
    {
    }

    public function __invoke(DeletePetSitterAdminCommand $command): void
    {
        $id = new PetSitterId($command->petSitterId);

        if (null === $this->petSitterRepository->findById($id)) {
            throw new PetSitterNotFoundException($command->petSitterId);
        }

        $this->petSitterRepository->remove($id);
    }
}
