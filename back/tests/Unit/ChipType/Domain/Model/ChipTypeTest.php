<?php

declare(strict_types=1);

namespace App\Tests\Unit\ChipType\Domain\Model;

use App\ChipType\Domain\Event\ChipTypeCreated;
use App\ChipType\Domain\Model\ChipColor;
use App\ChipType\Domain\Model\ChipType;
use App\ChipType\Domain\Model\ChipTypeId;
use PHPUnit\Framework\TestCase;

final class ChipTypeTest extends TestCase
{
    public function testCreateBuildsChipTypeAndRecordsEvent(): void
    {
        $id = ChipTypeId::generate();
        $chipType = ChipType::create(
            id: $id,
            name: 'Repas',
            color: new ChipColor('#22c55e'),
            ownerId: 'owner-1',
        );

        self::assertSame($id->value(), $chipType->id()->value());
        self::assertSame('Repas', $chipType->name());
        self::assertSame('#22c55e', $chipType->color()->value());
        self::assertSame('owner-1', $chipType->ownerId());

        $events = $chipType->pullDomainEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(ChipTypeCreated::class, $events[0]);
    }

    public function testChipColorAcceptsValidHexColors(): void
    {
        $color = new ChipColor('#aAbBcC');
        self::assertSame('#aAbBcC', $color->value());
    }

    public function testChipColorRejectsInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ChipColor('not-a-color');
    }

    public function testChipColorRejectsShortHex(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ChipColor('#abc');
    }

    public function testChipColorRejectsMissingHash(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new ChipColor('ff0000');
    }

    public function testRenameUpdatesName(): void
    {
        $chipType = ChipType::create(
            ChipTypeId::generate(),
            'Vétérinaire',
            new ChipColor('#ef4444'),
            'owner-1',
        );

        $chipType->rename('Visite médicale');

        self::assertSame('Visite médicale', $chipType->name());
    }

    public function testChangeColorUpdatesColor(): void
    {
        $chipType = ChipType::create(
            ChipTypeId::generate(),
            'Vaccin',
            new ChipColor('#3b82f6'),
            'owner-1',
        );

        $chipType->changeColor(new ChipColor('#0000ff'));

        self::assertSame('#0000ff', $chipType->color()->value());
    }
}
