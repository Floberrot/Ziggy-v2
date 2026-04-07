<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\PetSitter;
use App\Identity\Domain\Model\PetSitterId;

interface PetSitterRepository
{
    public function save(PetSitter $petSitter): void;

    public function findById(PetSitterId $id): ?PetSitter;

    /** @return list<PetSitter> */
    public function findByOwnerId(string $ownerId): array;

    /** @return list<PetSitter> */
    public function findAll(): array;

    /** @return list<PetSitter> */
    public function findAllPaginated(int $page, int $limit): array;

    public function countAll(): int;

    public function findByOwnerAndEmail(string $ownerId, string $inviteeEmail): ?PetSitter;

    public function remove(PetSitterId $id): void;
}
