<?php

declare(strict_types=1);

namespace App\Cat\Application\Command\UpdateCat;

use App\Cat\Domain\Exception\CatNotFoundException;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatName;
use App\Cat\Domain\Repository\CatRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateCatHandler
{
    public function __construct(private CatRepository $catRepository)
    {
    }

    public function __invoke(UpdateCatCommand $command): void
    {
        $cat = $this->catRepository->findById(new CatId($command->catId));

        if (null === $cat || $cat->ownerId() !== $command->requestingUserId) {
            throw new CatNotFoundException($command->catId);
        }

        $cat->rename(new CatName($command->name));
        $cat->updateWeight($command->weight);
        $cat->updateBreed($command->breed);
        $cat->updateColors($command->colors);

        $this->catRepository->save($cat);
    }
}
