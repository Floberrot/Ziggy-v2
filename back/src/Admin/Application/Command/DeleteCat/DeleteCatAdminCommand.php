<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\DeleteCat;

final readonly class DeleteCatAdminCommand
{
    public function __construct(public string $catId)
    {
    }
}
