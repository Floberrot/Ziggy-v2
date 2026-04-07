<?php

declare(strict_types=1);

namespace App\Admin\Application\Command\DeletePetSitter;

final readonly class DeletePetSitterAdminCommand
{
    public function __construct(public string $petSitterId)
    {
    }
}
