<?php

declare(strict_types=1);

namespace App\Tests\Unit\Cat\Domain\Model;

use App\Cat\Domain\Event\CatAdded;
use App\Cat\Domain\Model\Cat;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatName;
use PHPUnit\Framework\TestCase;

final class CatTest extends TestCase
{
    public function testAddCreatesACatAndRecordsEvent(): void
    {
        $id = CatId::generate();
        $cat = Cat::add(
            id: $id,
            name: new CatName('Ziggy'),
            ownerId: 'owner-1',
            weight: 4.2,
            breed: 'Tabby',
            colors: ['#ff6600'],
        );

        self::assertSame($id->value(), $cat->id()->value());
        self::assertSame('Ziggy', $cat->name()->value());
        self::assertSame('owner-1', $cat->ownerId());
        self::assertSame(4.2, $cat->weight());
        self::assertSame('Tabby', $cat->breed());
        self::assertSame(['#ff6600'], $cat->colors());

        $events = $cat->pullDomainEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(CatAdded::class, $events[0]);
    }

    public function testAddWithMinimalParametersUsesDefaults(): void
    {
        $cat = Cat::add(
            id: CatId::generate(),
            name: new CatName('Luna'),
            ownerId: 'owner-2',
        );

        self::assertNull($cat->weight());
        self::assertNull($cat->breed());
        self::assertEmpty($cat->colors());
    }

    public function testCatNameRejectsEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CatName('');
    }

    public function testCatNameRejectsWhitespaceOnly(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CatName('   ');
    }

    public function testCatNameRejectsTooLongString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new CatName(str_repeat('a', 101));
    }
}
