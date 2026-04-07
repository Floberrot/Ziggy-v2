<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\BusinessRuleException;

final class InvitationAlreadyDeclinedException extends BusinessRuleException
{
    public function __construct(string $token)
    {
        parent::__construct(sprintf('Invitation with token "%s" has already been declined.', $token));
    }
}
