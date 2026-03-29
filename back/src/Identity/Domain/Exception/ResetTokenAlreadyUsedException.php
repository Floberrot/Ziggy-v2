<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

final class ResetTokenAlreadyUsedException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This reset link has already been used.');
    }
}
