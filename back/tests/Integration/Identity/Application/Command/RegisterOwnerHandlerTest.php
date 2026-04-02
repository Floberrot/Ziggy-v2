<?php

declare(strict_types=1);

namespace App\Tests\Integration\Identity\Application\Command;

use App\Identity\Application\Command\RegisterOwner\RegisterOwnerCommand;
use App\Identity\Application\Command\RegisterOwner\RegisterOwnerHandler;
use App\Identity\Domain\Exception\EmailAlreadyRegisteredException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class RegisterOwnerHandlerTest extends KernelTestCase
{
    private RegisterOwnerHandler $handler;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(RegisterOwnerHandler::class);
        $this->userRepository = $container->get(UserRepository::class);
    }

    public function testRegistersOwnerSuccessfully(): void
    {
        ($this->handler)(new RegisterOwnerCommand(
            email: 'owner@test.com',
            plainPassword: 'SecurePass1!',
            username: 'testowner',
        ));

        $user = $this->userRepository->findByEmail(new Email('owner@test.com'));

        self::assertNotNull($user);
        self::assertSame('owner@test.com', $user->email()->value());
        self::assertSame('testowner', $user->username());
    }

    public function testThrowsWhenEmailAlreadyRegistered(): void
    {
        $command = new RegisterOwnerCommand(
            email: 'duplicate@test.com',
            plainPassword: 'SecurePass1!',
            username: 'duplicate',
        );

        ($this->handler)($command);

        $this->expectException(EmailAlreadyRegisteredException::class);
        ($this->handler)($command);
    }
}
