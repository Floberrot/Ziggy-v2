<?php

declare(strict_types=1);

namespace App\Calendar\Application\Command\PlaceChip;

final readonly class PlaceChipCommand
{
    public function __construct(
        public string $catId,
        public string $chipTypeId,
        public string $date,
        public string $authorId,
        public ?string $note = null,
    ) {
    }
}
