<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserRequest
{
    public function __construct(
        #[Assert\Length(max: 50)]
        public ?string $username = null,
    ) {
    }
}
