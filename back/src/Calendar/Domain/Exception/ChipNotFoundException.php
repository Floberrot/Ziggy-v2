<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class ChipNotFoundException extends NotFoundException
{
    public function __construct(string $chipId)
    {
        parent::__construct(sprintf('Chip "%s" not found.', $chipId));
    }
}
