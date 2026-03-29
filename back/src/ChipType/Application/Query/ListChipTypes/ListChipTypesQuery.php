<?php

declare(strict_types=1);

namespace App\ChipType\Application\Query\ListChipTypes;

final readonly class ListChipTypesQuery
{
    public function __construct(
        public readonly string $ownerId,
    ) {
    }
}
