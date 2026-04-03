<?php

declare(strict_types=1);

namespace App\Identity\Application\Port;

interface TokenGenerator
{
    public function generateForEmail(string $email): string;
}
