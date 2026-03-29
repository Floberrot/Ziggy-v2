<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\SendInvitation;

final readonly class SendInvitationCommand
{
    public function __construct(
        public string $ownerEmail,
        public string $inviteeEmail,
        public string $catId,
    ) {
    }
}
