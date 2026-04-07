<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\ChipType\Domain\Model\ChipType;
use App\ChipType\Domain\Model\ChipTypeId;
use App\ChipType\Domain\Repository\ChipTypeRepository;

final class InMemoryChipTypeRepository implements ChipTypeRepository
{
    /** @var array<string, ChipType> */
    private array $store = [];

    public function save(ChipType $chipType): void
    {
        $this->store[$chipType->id()->value()] = $chipType;
    }

    public function findById(ChipTypeId $id): ?ChipType
    {
        return $this->store[$id->value()] ?? null;
    }

    /** @return list<ChipType> */
    public function findByOwnerId(string $ownerId): array
    {
        return array_values(
            array_filter(
                $this->store,
                static fn (ChipType $ct) => $ct->ownerId() === $ownerId,
            )
        );
    }

    /** @return list<ChipType> */
    public function findAll(): array
    {
        return array_values($this->store);
    }

    /** @return list<ChipType> */
    public function findAllPaginated(int $page, int $limit): array
    {
        $all = array_values($this->store);

        return array_slice($all, ($page - 1) * $limit, $limit);
    }

    public function countAll(): int
    {
        return count($this->store);
    }

    public function remove(ChipTypeId $id): void
    {
        unset($this->store[$id->value()]);
    }
}
