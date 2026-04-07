<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\UpdateUser;

final readonly class UpdateUserAdminCommand
{
    public function __construct(
        public string $userId,
        public ?string $username,
    ) {
    }
}
