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
        #[Assert\DateTime(format: \DateTimeInterface::ATOM)]
        public string $dateTime,
        public ?string $note = null,
    ) {
    }
}
