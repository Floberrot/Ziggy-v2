<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

interface InvitationMailer
{
    public function sendInvitation(string $inviteeEmail, string $token): void;
}
