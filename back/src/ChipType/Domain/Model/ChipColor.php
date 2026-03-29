<?php

declare(strict_types=1);

namespace App\ChipType\Domain\Model;

final readonly class ChipColor
{
    private const string HEX_COLOR_REGEX = '/^#[0-9A-Fa-f]{6}$/';
    public function __construct(
        private string $value,
    ) {
        if (!preg_match(self::HEX_COLOR_REGEX, $value)) {
            throw new \InvalidArgumentException(
                sprintf('"%s" is not a valid hex color. Expected format: #RRGGBB.', $value),
            );
        }
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
