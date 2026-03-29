<?php

declare(strict_types=1);

namespace App\Identity\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class OwnerNotFoundException extends NotFoundException
{
    public function __construct()
    {
        parent::__construct('Owner not found.');
    }
}
