<?php

declare(strict_types=1);

namespace App\Calendar\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class CalendarNotFoundException extends NotFoundException
{
    public function __construct(string $catId)
    {
        parent::__construct(sprintf('Calendar for cat "%s" not found.', $catId));
    }
}
