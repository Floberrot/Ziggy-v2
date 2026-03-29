<?php

declare(strict_types=1);

namespace App\Cat\Application\Command\AddCat;

use App\Cat\Domain\Model\Cat;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatName;
use App\Cat\Domain\Repository\CatRepository;
use App\Shared\Domain\EventBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class AddCatHandler
{
    public function __construct(
        private CatRepository $catRepository,
        private EventBus $eventBus,
    ) {
    }

    public function __invoke(AddCatCommand $command): string
    {
        $catId = CatId::generate();

        $cat = Cat::add(
            id: $catId,
            name: new CatName($command->name),
            ownerId: $command->ownerId,
            weight: $command->weight,
            breed: $command->breed,
            colors: $command->colors,
        );

        $this->catRepository->save($cat);
        $this->eventBus->publish(...$cat->pullDomainEvents());

        return $catId->value();
    }
}
