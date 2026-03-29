<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AddCatRequest
{
    /** @param list<string> $colors */
    public function __construct(
        #[Assert\NotBlank]
        public string $name,
        public ?float $weight = null,
        public ?string $breed = null,
        public array $colors = [],
    ) {
    }
}
