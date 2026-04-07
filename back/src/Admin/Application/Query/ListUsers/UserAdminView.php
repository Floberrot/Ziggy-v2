<?php

declare(strict_types=1);

namespace App\Admin\Application\Query\ListUsers;

final readonly class UserAdminView
{
    public function __construct(
        public string $id,
        public string $email,
        public string $role,
        public ?string $username,
        public string $createdAt,
    ) {
    }

    /** @return array{id: string, email: string, role: string, username: string|null, createdAt: string} */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            'username' => $this->username,
            'createdAt' => $this->createdAt,
        ];
    }
}
