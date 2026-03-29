<?php

declare(strict_types=1);

namespace App\Identity\Domain\Model;

final readonly class UserId
{
    public function __construct(
        private string $value,
    ) {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('UserId cannot be empty.');
        }
    }

    public static function generate(): self
    {
        return new self(\Symfony\Component\Uid\Uuid::v7()->toRfc4122());
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
