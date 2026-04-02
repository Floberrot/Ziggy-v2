<?php

declare(strict_types=1);

namespace App\Tests\Integration\Cat\Application\Command;

use App\Cat\Application\Command\AddCat\AddCatCommand;
use App\Cat\Application\Command\AddCat\AddCatHandler;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Repository\CatRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AddCatHandlerTest extends KernelTestCase
{
    private AddCatHandler $handler;
    private CatRepository $catRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(AddCatHandler::class);
        $this->catRepository = $container->get(CatRepository::class);
    }

    public function testAddsCatAndReturnsId(): void
    {
        $catId = ($this->handler)(new AddCatCommand(
            ownerId: 'owner@test.com',
            name: 'Ziggy',
            weight: 4.2,
            breed: 'Tabby',
            colors: ['#ff6600'],
        ));

        self::assertNotEmpty($catId);

        $cat = $this->catRepository->findById(new CatId($catId));

        self::assertNotNull($cat);
        self::assertSame('Ziggy', $cat->name()->value());
        self::assertSame('owner@test.com', $cat->ownerId());
        self::assertSame(4.2, $cat->weight());
    }

    public function testAddsCatWithMinimalData(): void
    {
        $catId = ($this->handler)(new AddCatCommand(
            ownerId: 'owner@test.com',
            name: 'MinimalCat',
        ));

        $cat = $this->catRepository->findById(new CatId($catId));

        self::assertNotNull($cat);
        self::assertNull($cat->weight());
        self::assertNull($cat->breed());
        self::assertEmpty($cat->colors());
    }
}
