<?php

declare(strict_types=1);

namespace App\Cat\Application\Command\AddCat;

final readonly class AddCatCommand
{
    /** @param list<string> $colors */
    public function __construct(
        public string  $ownerId,
        public string  $name,
        public ?float  $weight = null,
        public ?string $breed = null,
        public array   $colors = [],
    )
    {
    }
}
