<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

final class CannotRevokeAcceptedInvitationException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Cannot revoke an already accepted invitation.');
    }
}
