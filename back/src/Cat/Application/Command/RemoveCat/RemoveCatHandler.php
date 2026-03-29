<?php

declare(strict_types=1);

namespace App\Cat\Application\Command\RemoveCat;

use App\Cat\Domain\Exception\CatNotFoundException;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Repository\CatRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RemoveCatHandler
{
    public function __construct(private CatRepository $catRepository)
    {
    }

    public function __invoke(RemoveCatCommand $command): void
    {
        $cat = $this->catRepository->findById(new CatId($command->catId));

        if (null === $cat || $cat->ownerId() !== $command->requestingUserId) {
            throw new CatNotFoundException($command->catId);
        }

        $this->catRepository->remove(new CatId($command->catId));
    }
}
