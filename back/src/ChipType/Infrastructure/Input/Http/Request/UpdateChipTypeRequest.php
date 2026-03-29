<?php

declare(strict_types=1);

namespace App\ChipType\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateChipTypeRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        #[Assert\NotBlank]
        public string $color,
    ) {
    }
}
