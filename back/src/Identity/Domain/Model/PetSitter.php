<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

final class PetSitter
{
    private ?UserId $userId;
    private PetSitterType $type;
    private ?int $age;
    private ?string $phoneNumber;

    private function __construct(
        private readonly PetSitterId $id,
        private readonly UserId $ownerId,
        private readonly Email $inviteeEmail,
        private readonly \DateTimeImmutable $createdAt,
        ?UserId $userId,
        PetSitterType $type,
        ?int $age,
        ?string $phoneNumber,
    ) {
        $this->userId = $userId;
        $this->type = $type;
        $this->age = $age;
        $this->phoneNumber = $phoneNumber;
    }

    public static function create(
        PetSitterId $id,
        UserId $ownerId,
        Email $inviteeEmail,
        PetSitterType $type,
        ?int $age,
        ?string $phoneNumber,
    ): self {
        return new self(
            id: $id,
            ownerId: $ownerId,
            inviteeEmail: $inviteeEmail,
            createdAt: new \DateTimeImmutable(),
            userId: null,
            type: $type,
            age: $age,
            phoneNumber: $phoneNumber,
        );
    }

    public static function reconstruct(
        PetSitterId $id,
        UserId $ownerId,
        Email $inviteeEmail,
        \DateTimeImmutable $createdAt,
        ?UserId $userId,
        PetSitterType $type,
        ?int $age,
        ?string $phoneNumber,
    ): self {
        return new self(
            id: $id,
            ownerId: $ownerId,
            inviteeEmail: $inviteeEmail,
            createdAt: $createdAt,
            userId: $userId,
            type: $type,
            age: $age,
            phoneNumber: $phoneNumber,
        );
    }

    public function linkUser(UserId $userId): void
    {
        $this->userId = $userId;
    }

    public function updateData(PetSitterType $type, ?int $age, ?string $phoneNumber): void
    {
        $this->type = $type;
        $this->age = $age;
        $this->phoneNumber = $phoneNumber;
    }

    public function id(): PetSitterId
    {
        return $this->id;
    }

    public function ownerId(): UserId
    {
        return $this->ownerId;
    }

    public function inviteeEmail(): Email
    {
        return $this->inviteeEmail;
    }

    public function userId(): ?UserId
    {
        return $this->userId;
    }

    public function type(): PetSitterType
    {
        return $this->type;
    }

    public function age(): ?int
    {
        return $this->age;
    }

    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
