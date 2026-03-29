<?php

declare(strict_types=1);

namespace App\Cat\Application\Query\ListCats;

final readonly class ListCatsQuery
{
    public function __construct(
        public readonly string $ownerId,
    ) {
    }
}
