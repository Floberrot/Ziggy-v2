<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Role;
use App\Identity\Domain\Model\User;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\UserRepository;
use App\Tests\Functional\AuthenticatedWebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class AdminLoginControllerTest extends AuthenticatedWebTestCase
{
    private static string $adminEmail = 'adminlogintest@functional.test';
    private static string $adminPassword = 'AdminLoginPass1!';
    private static string $adminSecret = 'change_me';

    private function createAdminUser(): void
    {
        $container = static::getContainer();
        /** @var UserRepository $repo */
        $repo = $container->get(UserRepository::class);

        if (null !== $repo->findByEmail(new Email(self::$adminEmail))) {
            return;
        }

        /** @var UserPasswordHasherInterface $hasher */
        $hasher = $container->get(UserPasswordHasherInterface::class);
        $hashed = $hasher->hashPassword(new InMemoryUser(self::$adminEmail, ''), self::$adminPassword);

        $repo->save(User::register(
            id: UserId::generate(),
            email: new Email(self::$adminEmail),
            hashedPassword: $hashed,
            role: Role::ADMIN,
            username: 'adminlogintest',
        ));
    }

    public function testAdminLoginSucceeds(): void
    {
        $this->createAdminUser();
        $client = static::createClient();

        $client->request('POST', '/api/admin/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => self::$adminEmail,
            'password' => self::$adminPassword,
            'adminSecret' => self::$adminSecret,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseIsSuccessful();

        /** @var array{token?: string} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        self::assertArrayHasKey('token', $data);
        self::assertNotEmpty($data['token']);
    }

    public function testAdminLoginFailsWithWrongAdminSecret(): void
    {
        $this->createAdminUser();
        $client = static::createClient();

        $client->request('POST', '/api/admin/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => self::$adminEmail,
            'password' => self::$adminPassword,
            'adminSecret' => 'wrong_secret',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(401);
    }

    public function testAdminLoginFailsWithWrongPassword(): void
    {
        $this->createAdminUser();
        $client = static::createClient();

        $client->request('POST', '/api/admin/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => self::$adminEmail,
            'password' => 'wrong_password',
            'adminSecret' => self::$adminSecret,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(401);
    }

    public function testAdminLoginFailsForNonAdminUser(): void
    {
        $client = static::createClient();

        // Register a regular owner
        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'regularuser@functional.test',
            'password' => 'RegularPass1!',
            'username' => 'regularuser',
        ], JSON_THROW_ON_ERROR));

        $client->request('POST', '/api/admin/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'regularuser@functional.test',
            'password' => 'RegularPass1!',
            'adminSecret' => self::$adminSecret,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(403);
    }
}
