<?php

declare(strict_types=1);

namespace App\ChipType\Application\Command\CreateChipType;

use App\ChipType\Domain\Model\ChipColor;
use App\ChipType\Domain\Model\ChipType;
use App\ChipType\Domain\Model\ChipTypeId;
use App\ChipType\Domain\Repository\ChipTypeRepository;
use App\Shared\Domain\EventBus;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class CreateChipTypeHandler
{
    public function __construct(
        private ChipTypeRepository $chipTypeRepository,
        private EventBus $eventBus,
    ) {
    }

    public function __invoke(CreateChipTypeCommand $command): string
    {
        $chipTypeId = ChipTypeId::generate();

        $chipType = ChipType::create(
            id: $chipTypeId,
            name: $command->name,
            color: new ChipColor($command->color),
            ownerId: $command->ownerId,
        );

        $this->chipTypeRepository->save($chipType);
        $this->eventBus->publish(...$chipType->pullDomainEvents());

        return $chipTypeId->value();
    }
}
