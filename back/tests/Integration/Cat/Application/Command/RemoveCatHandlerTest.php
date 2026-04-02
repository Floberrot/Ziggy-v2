<?php

declare(strict_types=1);

namespace App\Tests\Integration\Cat\Application\Command;

use App\Cat\Application\Command\AddCat\AddCatCommand;
use App\Cat\Application\Command\AddCat\AddCatHandler;
use App\Cat\Application\Command\RemoveCat\RemoveCatCommand;
use App\Cat\Application\Command\RemoveCat\RemoveCatHandler;
use App\Cat\Domain\Exception\CatNotFoundException;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Repository\CatRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class RemoveCatHandlerTest extends KernelTestCase
{
    private AddCatHandler $addHandler;
    private RemoveCatHandler $removeHandler;
    private CatRepository $catRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->addHandler = $container->get(AddCatHandler::class);
        $this->removeHandler = $container->get(RemoveCatHandler::class);
        $this->catRepository = $container->get(CatRepository::class);
    }

    public function testRemovesCatSuccessfully(): void
    {
        $ownerId = 'owner@test.com';

        $catId = ($this->addHandler)(new AddCatCommand(
            ownerId: $ownerId,
            name: 'ToBeRemoved',
        ));

        ($this->removeHandler)(new RemoveCatCommand(
            catId: $catId,
            requestingUserId: $ownerId,
        ));

        $cat = $this->catRepository->findById(new CatId($catId));
        self::assertNull($cat);
    }

    public function testThrowsWhenCatNotFound(): void
    {
        $this->expectException(CatNotFoundException::class);

        ($this->removeHandler)(new RemoveCatCommand(
            catId: (string) \Symfony\Component\Uid\Uuid::v4(),
            requestingUserId: 'owner@test.com',
        ));
    }

    public function testThrowsWhenWrongOwnerTriesToRemove(): void
    {
        $catId = ($this->addHandler)(new AddCatCommand(
            ownerId: 'real-owner@test.com',
            name: 'ProtectedCat',
        ));

        $this->expectException(CatNotFoundException::class);

        ($this->removeHandler)(new RemoveCatCommand(
            catId: $catId,
            requestingUserId: 'attacker@test.com',
        ));
    }
}
