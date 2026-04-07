<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\DeleteChipType;

final readonly class DeleteChipTypeAdminCommand
{
    public function __construct(public string $chipTypeId)
    {
    }
}
