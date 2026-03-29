<?php

declare(strict_types=1);

namespace App\Cat\Domain\Model;

final readonly class CatName
{
    public function __construct(
        private string $value,
    ) {
        $trimmed = trim($value);
        if ('' === $trimmed) {
            throw new \InvalidArgumentException('Cat name cannot be empty.');
        }

        if (strlen($trimmed) > 100) {
            throw new \InvalidArgumentException('Cat name cannot exceed 100 characters.');
        }
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
