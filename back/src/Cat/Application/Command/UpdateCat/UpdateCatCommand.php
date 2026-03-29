<?php

declare(strict_types=1);

namespace App\Cat\Application\Command\UpdateCat;

final readonly class UpdateCatCommand
{
    /** @param list<string> $colors */
    public function __construct(
        public string $catId,
        public string $requestingUserId,
        public string $name,
        public ?float $weight,
        public ?string $breed,
        public array $colors,
    ) {
    }
}
