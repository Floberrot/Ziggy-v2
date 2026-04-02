<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\Invitation;

interface InvitationRepository
{
    public function save(Invitation $invitation): void;

    public function findById(string $id): ?Invitation;

    public function findByToken(string $token): ?Invitation;

    /** @return list<Invitation> */
    public function findByOwnerId(string $ownerId): array;

    public function markAccepted(string $token): void;

    public function markDeclined(string $token): void;

    /** @return list<Invitation> */
    public function findPendingByOwnerAndEmail(string $ownerId, string $inviteeEmail): array;

    public function remove(string $id): void;
}
