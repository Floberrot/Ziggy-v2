<?php

declare(strict_types=1);

namespace App\Admin\Domain\Model;

use Symfony\Component\Uid\Uuid;

final readonly class ActivityLogId
{
    public function __construct(private string $value)
    {
    }

    public static function generate(): self
    {
        return new self(Uuid::v7()->toRfc4122());
    }

    public function value(): string
    {
        return $this->value;
    }
}
