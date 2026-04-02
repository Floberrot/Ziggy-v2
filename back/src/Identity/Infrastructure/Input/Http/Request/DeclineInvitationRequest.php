<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class DeclineInvitationRequest
{
    public function __construct(
        #[Assert\NotBlank]
        public string $token,
    ) {
    }
}
