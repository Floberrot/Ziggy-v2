<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\UpdateUsername;

final readonly class UpdateUsernameCommand
{
    public function __construct(
        public string $userId,
        public string $username,
    ) {
    }
}
