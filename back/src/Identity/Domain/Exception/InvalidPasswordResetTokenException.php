<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

final class InvalidPasswordResetTokenException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('Invalid or expired reset token.');
    }
}
