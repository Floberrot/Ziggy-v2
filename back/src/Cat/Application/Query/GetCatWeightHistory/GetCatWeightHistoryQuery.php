<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\GetCatWeightHistory;

final readonly class GetCatWeightHistoryQuery
{
    public function __construct(
        public string $catId,
        public string $requestingUserId,
    ) {
    }
}
