<?php

declare(strict_types=1);

namespace App\ChipType\Application\Command\RemoveChipType;

use App\ChipType\Domain\Exception\ChipTypeNotFoundException;
use App\ChipType\Domain\Model\ChipTypeId;
use App\ChipType\Domain\Repository\ChipTypeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class RemoveChipTypeHandler
{
    public function __construct(private ChipTypeRepository $chipTypeRepository)
    {
    }

    public function __invoke(RemoveChipTypeCommand $command): void
    {
        $chipType = $this->chipTypeRepository->findById(new ChipTypeId($command->chipTypeId));

        if (null === $chipType || $chipType->ownerId() !== $command->requestingUserId) {
            throw new ChipTypeNotFoundException($command->chipTypeId);
        }

        $this->chipTypeRepository->remove(new ChipTypeId($command->chipTypeId));
    }
}
