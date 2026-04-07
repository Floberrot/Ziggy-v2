<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\Identity\Domain\Model\PetSitter;
use App\Identity\Domain\Model\PetSitterId;
use App\Identity\Domain\Repository\PetSitterRepository;

final class InMemoryPetSitterRepository implements PetSitterRepository
{
    /** @var array<string, PetSitter> */
    private array $store = [];

    public function save(PetSitter $petSitter): void
    {
        $this->store[$petSitter->id()->value()] = $petSitter;
    }

    public function findById(PetSitterId $id): ?PetSitter
    {
        return $this->store[$id->value()] ?? null;
    }

    /** @return list<PetSitter> */
    public function findByOwnerId(string $ownerId): array
    {
        return array_values(
            array_filter(
                $this->store,
                static fn (PetSitter $ps) => $ps->ownerId()->value() === $ownerId,
            )
        );
    }

    /** @return list<PetSitter> */
    public function findAll(): array
    {
        return array_values($this->store);
    }

    /** @return list<PetSitter> */
    public function findAllPaginated(int $page, int $limit): array
    {
        $all = array_values($this->store);

        return array_slice($all, ($page - 1) * $limit, $limit);
    }

    public function countAll(): int
    {
        return count($this->store);
    }

    public function findByOwnerAndEmail(string $ownerId, string $inviteeEmail): ?PetSitter
    {
        foreach ($this->store as $ps) {
            if ($ps->ownerId()->value() === $ownerId && $ps->inviteeEmail()->value() === $inviteeEmail) {
                return $ps;
            }
        }

        return null;
    }

    public function remove(PetSitterId $id): void
    {
        unset($this->store[$id->value()]);
    }
}
