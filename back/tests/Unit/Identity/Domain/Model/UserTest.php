<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Model;

use App\Identity\Domain\Event\UserRegistered;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    public function testRegisterCreatesUserAndRecordsEvent(): void
    {
        $id = UserId::generate();
        $email = new Email('alice@example.com');

        $user = User::register(
            id: $id,
            email: $email,
            hashedPassword: 'hashed',
            role: Role::OWNER,
            username: 'alice',
        );

        self::assertSame($id->value(), $user->id()->value());
        self::assertSame('alice@example.com', $user->email()->value());
        self::assertSame('hashed', $user->hashedPassword());
        self::assertSame(Role::OWNER, $user->role());
        self::assertSame('alice', $user->username());

        $events = $user->pullDomainEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(UserRegistered::class, $events[0]);
        self::assertSame('alice@example.com', $events[0]->email);
    }

    public function testUpdateUsernameChangesUsername(): void
    {
        $user = User::register(
            id: UserId::generate(),
            email: new Email('bob@example.com'),
            hashedPassword: 'hashed',
            role: Role::PET_SITTER,
            username: 'bob',
        );

        $user->updateUsername('robert');

        self::assertSame('robert', $user->username());
    }

    public function testChangePasswordUpdatesHashedPassword(): void
    {
        $user = User::register(
            id: UserId::generate(),
            email: new Email('alice@example.com'),
            hashedPassword: 'old-hash',
            role: Role::OWNER,
            username: 'alice',
        );

        $user->changePassword('new-hash');

        self::assertSame('new-hash', $user->hashedPassword());
    }

    public function testReconstituteDoesNotRecordEvent(): void
    {
        $user = User::reconstitute(
            id: UserId::generate(),
            email: new Email('alice@example.com'),
            hashedPassword: 'hashed',
            role: Role::OWNER,
            username: 'alice',
            createdAt: new \DateTimeImmutable('2025-01-01'),
        );

        self::assertEmpty($user->pullDomainEvents());
        self::assertSame('2025-01-01', $user->createdAt()->format('Y-m-d'));
    }

    public function testReconstituteAcceptsNullUsername(): void
    {
        $user = User::reconstitute(
            id: UserId::generate(),
            email: new Email('alice@example.com'),
            hashedPassword: 'hashed',
            role: Role::ADMIN,
            username: null,
            createdAt: new \DateTimeImmutable(),
        );

        self::assertNull($user->username());
    }
}
