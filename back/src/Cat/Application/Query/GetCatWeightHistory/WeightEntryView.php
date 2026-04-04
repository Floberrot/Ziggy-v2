<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\GetCatWeightHistory;

final readonly class WeightEntryView
{
    public function __construct(
        public float $weight,
        public string $recordedAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'weight' => $this->weight,
            'recordedAt' => $this->recordedAt,
        ];
    }
}
