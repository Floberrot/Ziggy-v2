<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\UpdatePetSitter;

final readonly class UpdatePetSitterCommand
{
    public function __construct(
        public string $petSitterId,
        public string $ownerEmail,
        public string $type,
        public ?int $age,
        public ?string $phoneNumber,
    ) {
    }
}
