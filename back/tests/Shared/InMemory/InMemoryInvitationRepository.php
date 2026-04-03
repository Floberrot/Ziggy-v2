<?php

declare(strict_types=1);

namespace App\Tests\Shared\InMemory;

use App\Identity\Domain\Model\Invitation;
use App\Identity\Domain\Repository\InvitationRepository;

final class InMemoryInvitationRepository implements InvitationRepository
{
    /** @var array<string, Invitation> */
    private array $store = [];

    public function save(Invitation $invitation): void
    {
        $this->store[$invitation->id()] = $invitation;
    }

    public function findById(string $id): ?Invitation
    {
        return $this->store[$id] ?? null;
    }

    public function findByToken(string $token): ?Invitation
    {
        foreach ($this->store as $invitation) {
            if ($invitation->token() === $token) {
                return $invitation;
            }
        }

        return null;
    }

    /** @return list<Invitation> */
    public function findByOwnerId(string $ownerId): array
    {
        return array_values(
            array_filter(
                $this->store,
                static fn (Invitation $i) => $i->ownerId()->value() === $ownerId,
            )
        );
    }

    public function markAccepted(string $token): void
    {
        foreach ($this->store as $id => $invitation) {
            if ($invitation->token() === $token) {
                $this->store[$id] = Invitation::reconstruct(
                    id: $invitation->id(),
                    ownerId: $invitation->ownerId(),
                    inviteeEmail: $invitation->inviteeEmail(),
                    catId: $invitation->catId(),
                    token: $invitation->token(),
                    expiresAt: $invitation->expiresAt(),
                    accepted: true,
                );

                return;
            }
        }
    }

    public function remove(string $id): void
    {
        unset($this->store[$id]);
    }
}
