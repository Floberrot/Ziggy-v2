<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Output\Persistence;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invitations')]
class InvitationOrmEntity
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(type: 'string', length: 36)]
    private string $ownerId;

    #[ORM\Column(type: 'string', length: 180)]
    private string $inviteeEmail;

    #[ORM\Column(type: 'string', length: 36)]
    private string $catId;

    #[ORM\Column(type: 'string', length: 64, unique: true)]
    private string $token;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $expiresAt;

    #[ORM\Column(type: 'boolean')]
    private bool $accepted = false;

    public function getId(): string
    {
        return $this->id;
    }
    public function setId(string $id): void
    {
        $this->id = $id;
    }
    public function getOwnerId(): string
    {
        return $this->ownerId;
    }
    public function setOwnerId(string $ownerId): void
    {
        $this->ownerId = $ownerId;
    }
    public function getInviteeEmail(): string
    {
        return $this->inviteeEmail;
    }
    public function setInviteeEmail(string $inviteeEmail): void
    {
        $this->inviteeEmail = $inviteeEmail;
    }
    public function getCatId(): string
    {
        return $this->catId;
    }
    public function setCatId(string $catId): void
    {
        $this->catId = $catId;
    }
    public function getToken(): string
    {
        return $this->token;
    }
    public function setToken(string $token): void
    {
        $this->token = $token;
    }
    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }
    public function setExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->expiresAt = $expiresAt;
    }
    public function isAccepted(): bool
    {
        return $this->accepted;
    }
    public function setAccepted(bool $accepted): void
    {
        $this->accepted = $accepted;
    }
}
