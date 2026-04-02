<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

enum PetSitterType: string
{
    case Family = 'family';
    case Friend = 'friend';
    case Professional = 'professional';
}
