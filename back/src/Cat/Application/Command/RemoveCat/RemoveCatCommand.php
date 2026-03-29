<?php

declare(strict_types=1);

namespace App\Cat\Application\Command\RemoveCat;

final readonly class RemoveCatCommand
{
    public function __construct(
        public string $catId,
        public string $requestingUserId,
    ) {
    }
}
