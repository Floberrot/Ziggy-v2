<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\AcceptInvitation;

final readonly class AcceptInvitationResult
{
    public function __construct(public string $email)
    {
    }
}
