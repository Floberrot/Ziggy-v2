<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

use App\Identity\Domain\Event\UserRegistered;
use App\Shared\Domain\AggregateRoot;

final class User extends AggregateRoot
{
    private function __construct(
        private readonly UserId $id,
        private readonly Email $email,
        private string $hashedPassword,
        private readonly Role $role,
        private ?string $username,
        private readonly \DateTimeImmutable $createdAt,
    ) {
    }

    public static function register(
        UserId $id,
        Email $email,
        string $hashedPassword,
        Role $role,
        string $username,
    ): self {
        $user = new self(
            id: $id,
            email: $email,
            hashedPassword: $hashedPassword,
            role: $role,
            username: $username,
            createdAt: new \DateTimeImmutable(),
        );

        $user->recordEvent(new UserRegistered(
            userId: $id->value(),
            email: $email->value(),
            role: $role->value,
            occurredAt: $user->createdAt,
        ));

        return $user;
    }

    public static function reconstitute(
        UserId $id,
        Email $email,
        string $hashedPassword,
        Role $role,
        ?string $username,
        \DateTimeImmutable $createdAt,
    ): self {
        return new self(
            id: $id,
            email: $email,
            hashedPassword: $hashedPassword,
            role: $role,
            username: $username,
            createdAt: $createdAt,
        );
    }

    public function id(): UserId
    {
        return $this->id;
    }

    public function email(): Email
    {
        return $this->email;
    }

    public function hashedPassword(): string
    {
        return $this->hashedPassword;
    }

    public function role(): Role
    {
        return $this->role;
    }

    public function username(): ?string
    {
        return $this->username;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updateUsername(string $username): void
    {
        $this->username = $username;
    }

    public function changePassword(string $hashedPassword): void
    {
        $this->hashedPassword = $hashedPassword;
    }
}
