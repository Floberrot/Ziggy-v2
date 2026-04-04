<?php

declare(strict_types=1);

namespace App\Cat\Domain\Model;

final readonly class CatWeightEntry
{
    public function __construct(
        public CatWeightEntryId $id,
        public CatId $catId,
        public float $weight,
        public \DateTimeImmutable $recordedAt,
    ) {
    }
}
