<?php

declare(strict_types=1);

namespace App\ChipType\Domain\Model;

use Symfony\Component\Uid\Uuid;

final readonly class ChipTypeId
{
    public function __construct(
        private string $value,
    ) {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('ChipTypeId cannot be empty.');
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
