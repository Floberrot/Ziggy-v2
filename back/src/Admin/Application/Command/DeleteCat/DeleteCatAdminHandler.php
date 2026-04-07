<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\DeleteCat;

use App\Cat\Domain\Exception\CatNotFoundException;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Repository\CatRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(bus: 'command.bus')]
final readonly class DeleteCatAdminHandler
{
    public function __construct(private CatRepository $catRepository)
    {
    }

    public function __invoke(DeleteCatAdminCommand $command): void
    {
        $catId = new CatId($command->catId);

        if (null === $this->catRepository->findById($catId)) {
            throw new CatNotFoundException($command->catId);
        }

        $this->catRepository->remove($catId);
    }
}
