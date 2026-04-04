<?php

declare(strict_types=1);

namespace App\Cat\Application\Command\UpdateCat;

use App\Cat\Domain\Exception\CatNotFoundException;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatName;
use App\Cat\Domain\Model\CatWeightEntry;
use App\Cat\Domain\Model\CatWeightEntryId;
use App\Cat\Domain\Repository\CatRepository;
use App\Cat\Domain\Repository\CatWeightRepository;
use App\Shared\Domain\EventBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class UpdateCatHandler
{
    public function __construct(
        private CatRepository $catRepository,
        private CatWeightRepository $catWeightRepository,
        private EventBus $eventBus,
    ) {
    }

    public function __invoke(UpdateCatCommand $command): void
    {
        $cat = $this->catRepository->findById(new CatId($command->catId));

        if (null === $cat || $cat->ownerId() !== $command->requestingUserId) {
            throw new CatNotFoundException($command->catId);
        }

        $cat->update(
            name: new CatName($command->name),
            weight: $command->weight,
            breed: $command->breed,
            colors: $command->colors,
        );

        $this->catRepository->save($cat);
        $this->eventBus->publish(...$cat->pullDomainEvents());

        if (null !== $command->weight) {
            $this->catWeightRepository->save(new CatWeightEntry(
                id: CatWeightEntryId::generate(),
                catId: new CatId($command->catId),
                weight: $command->weight,
                recordedAt: new \DateTimeImmutable(),
            ));
        }
    }
}
