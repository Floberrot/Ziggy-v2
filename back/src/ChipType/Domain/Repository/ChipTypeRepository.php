<?php

declare(strict_types=1);

namespace App\ChipType\Domain\Repository;

use App\ChipType\Domain\Model\ChipType;
use App\ChipType\Domain\Model\ChipTypeId;

interface ChipTypeRepository
{
    public function save(ChipType $chipType): void;

    public function findById(ChipTypeId $id): ?ChipType;

    /** @return list<ChipType> */
    public function findByOwnerId(string $ownerId): array;

    public function remove(ChipTypeId $id): void;
}
