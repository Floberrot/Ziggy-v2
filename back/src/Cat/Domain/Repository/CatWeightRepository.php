<?php

declare(strict_types=1);

namespace App\Cat\Domain\Repository;

use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Model\CatWeightEntry;

interface CatWeightRepository
{
    public function save(CatWeightEntry $entry): void;

    /** @return list<CatWeightEntry> */
    public function findByCatId(CatId $catId): array;
}
