<?php

declare(strict_types=1);

namespace App\Identity\Application\Command\AcceptInvitation;

use SensitiveParameter;

final readonly class AcceptInvitationCommand
{
    public function __construct(
        #[SensitiveParameter]
        public string $token,
        #[SensitiveParameter]
        public string $plainPassword,
    ) {
    }
}
