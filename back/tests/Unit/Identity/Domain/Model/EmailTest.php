<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Model;

use App\Identity\Domain\Model\Email;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
    public function testValidEmailIsAccepted(): void
    {
        $email = new Email('user@example.com');

        self::assertSame('user@example.com', $email->value());
        self::assertSame('user@example.com', (string) $email);
    }

    public function testEmailRejectsInvalidAddress(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('not-an-email');
    }

    public function testEmailRejectsMissingAt(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('useratexample.com');
    }

    public function testEmailRejectsEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Email('');
    }

    public function testEmailIsCaseSensitiveOnValue(): void
    {
        $email = new Email('User@Example.COM');

        self::assertSame('User@Example.COM', $email->value());
    }
}
