<?php

declare(strict_types=1);

namespace App\Identity\Application\Query\ListInvitations;

final readonly class ListInvitationsQuery
{
    public function __construct(public string $ownerEmail)
    {
    }
}
