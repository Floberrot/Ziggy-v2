<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\BusinessRuleException;

final class InvalidPasswordResetTokenException extends BusinessRuleException
{
    public function __construct(string $token)
    {
        parent::__construct(sprintf('Invalid or expired reset token "%s".', $token));
    }
}
