<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\BusinessRuleException;

final class CannotRevokeAcceptedInvitationException extends BusinessRuleException
{
    public function __construct(string $invitationId)
    {
        parent::__construct(sprintf('Cannot revoke already accepted invitation "%s".', $invitationId));
    }
}
