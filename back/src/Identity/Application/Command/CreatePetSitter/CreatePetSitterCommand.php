<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\CreatePetSitter;

final readonly class CreatePetSitterCommand
{
    public function __construct(
        public string $ownerEmail,
        public string $inviteeEmail,
        public string $catId,
        public string $type,
        public ?int $age,
        public ?string $phoneNumber,
    ) {
    }
}
