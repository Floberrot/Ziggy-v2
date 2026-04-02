<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\ListPetSitters;

final readonly class ListPetSittersQuery
{
    public function __construct(
        public string $ownerEmail,
    ) {
    }
}
