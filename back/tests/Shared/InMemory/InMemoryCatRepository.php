<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\Cat\Domain\Model\Cat;
use App\Cat\Domain\Model\CatId;
use App\Cat\Domain\Repository\CatRepository;

final class InMemoryCatRepository implements CatRepository
{
    /** @var array<string, Cat> */
    private array $store = [];

    public function save(Cat $cat): void
    {
        $this->store[$cat->id()->value()] = $cat;
    }

    public function findById(CatId $id): ?Cat
    {
        return $this->store[$id->value()] ?? null;
    }

    /** @return list<Cat> */
    public function findByOwnerId(string $ownerId): array
    {
        return array_values(
            array_filter(
                $this->store,
                static fn (Cat $cat) => $cat->ownerId() === $ownerId,
            )
        );
    }

    /** @return list<Cat> */
    public function findAll(): array
    {
        return array_values($this->store);
    }

    /** @return list<Cat> */
    public function findAllPaginated(int $page, int $limit): array
    {
        $all = array_values($this->store);

        return array_slice($all, ($page - 1) * $limit, $limit);
    }

    public function countAll(): int
    {
        return count($this->store);
    }

    public function remove(CatId $id): void
    {
        unset($this->store[$id->value()]);
    }
}
