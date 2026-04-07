<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class OwnerNotFoundException extends NotFoundException
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Owner with email "%s" not found.', $email));
    }
}
