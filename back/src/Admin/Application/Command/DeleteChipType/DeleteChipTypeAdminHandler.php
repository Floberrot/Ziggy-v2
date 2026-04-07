<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\DeleteChipType;

use App\ChipType\Domain\Exception\ChipTypeNotFoundException;
use App\ChipType\Domain\Model\ChipTypeId;
use App\ChipType\Domain\Repository\ChipTypeRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteChipTypeAdminHandler
{
    public function __construct(private ChipTypeRepository $chipTypeRepository)
    {
    }

    public function __invoke(DeleteChipTypeAdminCommand $command): void
    {
        $id = new ChipTypeId($command->chipTypeId);

        if (null === $this->chipTypeRepository->findById($id)) {
            throw new ChipTypeNotFoundException($command->chipTypeId);
        }

        $this->chipTypeRepository->remove($id);
    }
}
