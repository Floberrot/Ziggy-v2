<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class ChipTypeAlreadyScheduledException extends ConflictException
{
    public function __construct(string $chipTypeId)
    {
        parent::__construct(sprintf('Chip type "%s" is already scheduled for this calendar.', $chipTypeId));
    }
}
