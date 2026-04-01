<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\BusinessRuleException;

final class ResetTokenExpiredException extends BusinessRuleException
{
    public function __construct()
    {
        parent::__construct('This reset link has expired.');
    }
}
