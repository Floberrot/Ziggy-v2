<?php

declare(strict_types=1);

namespace App\Identity\Domain\Repository;

use App\Identity\Domain\Model\PasswordResetToken;

interface PasswordResetTokenRepository
{
    public function save(PasswordResetToken $token): void;

    public function findByToken(string $token): ?PasswordResetToken;

    public function markUsed(string $token): void;
}
