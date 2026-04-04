<?php

declare(strict_types=1);

namespace App\Cat\Domain\Model;

use Symfony\Component\Uid\Uuid;

final readonly class CatWeightEntryId
{
    public function __construct(
        private string $value,
    ) {
        if ('' === trim($value)) {
            throw new \InvalidArgumentException('CatWeightEntryId cannot be empty.');
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
