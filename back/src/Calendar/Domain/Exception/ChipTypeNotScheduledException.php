<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class ChipTypeNotScheduledException extends NotFoundException
{
    public function __construct(string $chipTypeId)
    {
        parent::__construct(sprintf('Chip type "%s" is not scheduled for this calendar.', $chipTypeId));
    }
}
