<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class AdminLoginRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $email,
        #[Assert\NotBlank]
        public string $password,
        #[Assert\NotBlank]
        public string $adminSecret,
    ) {
    }
}
