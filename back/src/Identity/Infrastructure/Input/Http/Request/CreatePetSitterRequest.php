<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePetSitterRequest
{
    public function __construct(
        #[Assert\NotBlank]
        #[Assert\Email]
        public string $inviteeEmail,
        #[Assert\NotBlank]
        public string $catId,
        #[Assert\NotBlank]
        #[Assert\Choice(choices: ['family', 'friend', 'professional'])]
        public string $type,
        #[Assert\PositiveOrZero]
        public ?int $age = null,
        public ?string $phoneNumber = null,
    ) {
    }
}
