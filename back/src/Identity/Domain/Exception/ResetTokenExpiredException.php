<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\BusinessRuleException;

final class ResetTokenExpiredException extends BusinessRuleException
{
    public function __construct(string $token)
    {
        parent::__construct(sprintf('Reset token "%s" has expired.', $token));
    }
}
