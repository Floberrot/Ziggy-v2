<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\DeclineInvitation;

final readonly class DeclineInvitationCommand
{
    public function __construct(
        public string $token,
    ) {
    }
}
