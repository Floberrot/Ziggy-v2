<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class AccountAlreadyExistsException extends ConflictException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('An account already exists for email "%s".', $email));
    }
}
