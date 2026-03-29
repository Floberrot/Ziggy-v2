<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetUser;

final readonly class UserView
{
    public function __construct(
        public string $id,
        public string $email,
        public string $role,
        public string $createdAt,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'createdAt' => $this->createdAt,
        ];
    }
}
