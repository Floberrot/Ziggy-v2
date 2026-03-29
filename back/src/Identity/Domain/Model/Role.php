<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

enum Role: string
{
    case OWNER = 'ROLE_OWNER';
    case PET_SITTER = 'ROLE_PET_SITTER';
    case ADMIN = 'ROLE_ADMIN';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Owner',
            self::PET_SITTER => 'Pet Sitter',
            self::ADMIN => 'Admin',
        };
    }
}
