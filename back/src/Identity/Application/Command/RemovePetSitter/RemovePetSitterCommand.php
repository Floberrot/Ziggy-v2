<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RemovePetSitter;

final readonly class RemovePetSitterCommand
{
    public function __construct(
        public string $petSitterId,
        public string $ownerEmail,
    ) {
    }
}
