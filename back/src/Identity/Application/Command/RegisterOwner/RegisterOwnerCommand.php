<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RegisterOwner;

final readonly class RegisterOwnerCommand
{
    public function __construct(
        public readonly string $email,
        public readonly string $plainPassword,
    ) {
    }
}
