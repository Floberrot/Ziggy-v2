<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListPetSitters;

final readonly class ListPetSittersAdminQuery
{
    public function __construct(
        public int $page = 1,
        public int $limit = 50,
    ) {
    }
}
