<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

final class InvitationExpiredException extends \DomainException
{
    public function __construct()
    {
        parent::__construct('This invitation has expired.');
    }
}
