<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

use Symfony\Component\Uid\Uuid;

final readonly class PetSitterId
{
    public function __construct(
        private string $value,
    ) {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('PetSitterId cannot be empty.');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::v7()->toRfc4122());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
