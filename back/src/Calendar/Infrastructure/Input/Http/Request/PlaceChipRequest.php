<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class PlaceChipRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $chipTypeId,
        #[Assert\NotBlank]
        public string $date,
        public ?string $note = null,
    ) {
    }
}
