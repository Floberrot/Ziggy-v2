<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\UpdateOwnerProfile;

final readonly class UpdateOwnerProfileCommand
{
    public function __construct(
        public string $ownerEmail,
        public ?int $age,
        public ?string $phoneNumber,
    ) {
    }
}
