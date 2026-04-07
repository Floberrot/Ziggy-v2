<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class InvitationNotFoundException extends NotFoundException
{
    public function __construct(string $token)
    {
        parent::__construct(sprintf('Invitation with token "%s" not found.', $token));
    }
}
