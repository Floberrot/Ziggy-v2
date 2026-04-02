<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\GetOwnerProfile;

final readonly class OwnerProfileView
{
    public function __construct(
        public string $userId,
        public string $email,
        public ?string $username,
        public ?int $age,
        public ?string $phoneNumber,
        public int $catsCount,
        public int $chipsCount,
    ) {
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'userId' => $this->userId,
            'email' => $this->email,
            'username' => $this->username,
            'age' => $this->age,
            'phoneNumber' => $this->phoneNumber,
            'catsCount' => $this->catsCount,
            'chipsCount' => $this->chipsCount,
        ];
    }
}
