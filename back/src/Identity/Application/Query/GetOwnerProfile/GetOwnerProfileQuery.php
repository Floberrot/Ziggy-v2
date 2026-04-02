<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetOwnerProfile;

final readonly class GetOwnerProfileQuery
{
    public function __construct(
        public string $ownerEmail,
    ) {
    }
}
