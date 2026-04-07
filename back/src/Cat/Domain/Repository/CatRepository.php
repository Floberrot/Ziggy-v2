<?php

declare(strict_types=1);

namespace App\Cat\Domain\Repository;

use App\Cat\Domain\Model\Cat;
use App\Cat\Domain\Model\CatId;

interface CatRepository
{
    public function save(Cat $cat): void;

    public function findById(CatId $id): ?Cat;

    /** @return list<Cat> */
    public function findByOwnerId(string $ownerId): array;

    /** @return list<Cat> */
    public function findAll(): array;

    /** @return list<Cat> */
    public function findAllPaginated(int $page, int $limit): array;

    public function countAll(): int;

    public function remove(CatId $id): void;
}
