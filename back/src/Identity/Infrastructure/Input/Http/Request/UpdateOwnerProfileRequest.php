<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateOwnerProfileRequest
{
    public function __construct(
        #[Assert\PositiveOrZero]
        public ?int $age = null,
        public ?string $phoneNumber = null,
    ) {
    }
}
