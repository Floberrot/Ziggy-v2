<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class SendInvitationRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $inviteeEmail,
        #[Assert\NotBlank]
        public string $catId,
    ) {
    }
}
