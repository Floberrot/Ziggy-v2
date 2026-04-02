<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\OwnerProfile;
use App\Identity\Domain\Model\UserId;

interface OwnerProfileRepository
{
    public function save(OwnerProfile $profile): void;

    public function findByUserId(UserId $userId): ?OwnerProfile;
}
