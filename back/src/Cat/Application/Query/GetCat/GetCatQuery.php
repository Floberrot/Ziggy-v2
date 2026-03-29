<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\GetCat;

final readonly class GetCatQuery
{
    public function __construct(
        public string $catId,
        public string $requestingUserId,
    ) {
    }
}
