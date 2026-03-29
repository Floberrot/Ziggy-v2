<?php

declare(strict_types=1);

namespace App\ChipType\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class ChipTypeNotFoundException extends NotFoundException
{
    public function __construct(string $chipTypeId)
    {
        parent::__construct(sprintf('Chip type "%s" not found.', $chipTypeId));
    }
}
