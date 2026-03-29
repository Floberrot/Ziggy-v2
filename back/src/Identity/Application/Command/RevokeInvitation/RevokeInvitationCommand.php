<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\RevokeInvitation;

final readonly class RevokeInvitationCommand
{
    public function __construct(
        public string $invitationId,
        public string $ownerEmail,
    ) {
    }
}
