<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\BusinessRuleException;

final class CannotRevokeAcceptedInvitationException extends BusinessRuleException
{
    public function __construct()
    {
        parent::__construct('Cannot revoke an already accepted invitation.');
    }
}
