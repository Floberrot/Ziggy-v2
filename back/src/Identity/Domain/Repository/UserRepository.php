<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;

interface UserRepository
{
    public function save(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    /** @return list<User> */
    public function findAll(): array;

    /** @return list<User> */
    public function findAllPaginated(int $page, int $limit): array;

    public function countAll(): int;

    public function remove(UserId $id): void;
}
