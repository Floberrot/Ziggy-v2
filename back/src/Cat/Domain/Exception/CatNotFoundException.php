<?php

declare(strict_types=1);

namespace App\Cat\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class CatNotFoundException extends NotFoundException
{
    public function __construct(string $catId)
    {
        parent::__construct(sprintf('Cat "%s" not found.', $catId));
    }
}
