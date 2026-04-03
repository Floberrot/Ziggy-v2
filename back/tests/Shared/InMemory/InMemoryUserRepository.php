<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;

final class InMemoryUserRepository implements UserRepository
{
    /** @var array<string, User> */
    private array $store = [];

    public function save(User $user): void
    {
        $this->store[$user->id()->value()] = $user;
    }

    public function findById(UserId $id): ?User
    {
        return $this->store[$id->value()] ?? null;
    }

    public function findByEmail(Email $email): ?User
    {
        foreach ($this->store as $user) {
            if ($user->email()->value() === $email->value()) {
                return $user;
            }
        }

        return null;
    }

    /** @return list<User> */
    public function findAll(): array
    {
        return array_values($this->store);
    }
}
