<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

final class OwnerProfile
{
    private ?int $age;
    private ?string $phoneNumber;

    private function __construct(
        private readonly UserId $userId,
        ?int $age,
        ?string $phoneNumber,
    ) {
        $this->age = $age;
        $this->phoneNumber = $phoneNumber;
    }

    public static function create(UserId $userId): self
    {
        return new self(
            userId: $userId,
            age: null,
            phoneNumber: null,
        );
    }

    public static function reconstruct(
        UserId $userId,
        ?int $age,
        ?string $phoneNumber,
    ): self {
        return new self(
            userId: $userId,
            age: $age,
            phoneNumber: $phoneNumber,
        );
    }

    public function update(?int $age, ?string $phoneNumber): void
    {
        $this->age = $age;
        $this->phoneNumber = $phoneNumber;
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function age(): ?int
    {
        return $this->age;
    }

    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
    }
}
