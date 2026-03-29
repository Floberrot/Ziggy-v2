<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class EmailAlreadyRegisteredException extends ConflictException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('User with email "%s" already exists.', $email));
    }
}
