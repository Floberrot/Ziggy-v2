<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    private static ?string $cachedToken = null;
    private static ?string $cachedUserId = null;
    private static ?string $cachedAdminToken = null;
    private ?KernelBrowser $testClient = null;
    private static string $testEmail = 'testowner@functional.test';
    private static string $testPassword = 'FunctionalPass1!';
    private static string $testUsername = 'functionalowner';

    private static string $adminEmail = 'testadmin@functional.test';
    private static string $adminPassword = 'AdminPass1!';
    private static string $adminUsername = 'functionaladmin';
    private static string $adminSecret = 'change_me';

    protected function setUp(): void
    {
        parent::setUp();
        self::$cachedToken = null;
        self::$cachedUserId = null;
        self::$cachedAdminToken = null;
    }

    protected function getTestClient(): KernelBrowser
    {
        if (null === $this->testClient) {
            $this->testClient = static::createClient();
        }

        return $this->testClient;
    }

    protected function createAuthenticatedClient(): KernelBrowser
    {
        $client = $this->getTestClient();

        $token = $this->getAuthToken($client);

        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);
        $client->setServerParameter('CONTENT_TYPE', 'application/json');

        return $client;
    }

    protected function createAdminClient(): KernelBrowser
    {
        $client = $this->getTestClient();

        $token = $this->getAdminToken($client);

        $client->setServerParameter('HTTP_Authorization', 'Bearer ' . $token);
        $client->setServerParameter('CONTENT_TYPE', 'application/json');

        return $client;
    }

    protected function getAuthToken(KernelBrowser $client): string
    {
        if (null !== self::$cachedToken) {
            return self::$cachedToken;
        }

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => self::$testEmail,
            'password' => self::$testPassword,
            'username' => self::$testUsername,
        ], JSON_THROW_ON_ERROR));

        $client->request('POST', '/api/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => self::$testEmail,
            'password' => self::$testPassword,
        ], JSON_THROW_ON_ERROR));

        $response = $client->getResponse();
        /** @var array{token: string} $data */
        $data = json_decode($response->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        self::$cachedToken = $data['token'];

        return self::$cachedToken;
    }

    protected function getAdminToken(KernelBrowser $client): string
    {
        if (null !== self::$cachedAdminToken) {
            return self::$cachedAdminToken;
        }

        // Create an admin user via the console command approach (use the repository directly)
        $container = static::getContainer();
        /** @var \App\Identity\Domain\Repository\UserRepository $userRepository */
        $userRepository = $container->get(\App\Identity\Domain\Repository\UserRepository::class);
        /** @var \Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface $hasher */
        $hasher = $container->get(\Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface::class);

        $existingUser = $userRepository->findByEmail(new \App\Identity\Domain\Model\Email(self::$adminEmail));

        if (null === $existingUser) {
            $hashedPassword = $hasher->hashPassword(
                new \Symfony\Component\Security\Core\User\InMemoryUser(self::$adminEmail, ''),
                self::$adminPassword,
            );

            $admin = \App\Identity\Domain\Model\User::register(
                id: \App\Identity\Domain\Model\UserId::generate(),
                email: new \App\Identity\Domain\Model\Email(self::$adminEmail),
                hashedPassword: $hashedPassword,
                role: \App\Identity\Domain\Model\Role::ADMIN,
                username: self::$adminUsername,
            );

            $userRepository->save($admin);
        }

        $client->request('POST', '/api/admin/auth/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => self::$adminEmail,
            'password' => self::$adminPassword,
            'adminSecret' => self::$adminSecret,
        ], JSON_THROW_ON_ERROR));

        $response = $client->getResponse();
        /** @var array{token: string} $data */
        $data = json_decode($response->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);
        self::$cachedAdminToken = $data['token'];

        return self::$cachedAdminToken;
    }

    protected function getTestEmail(): string
    {
        return self::$testEmail;
    }

    public static function tearDownAfterClass(): void
    {
        self::$cachedToken = null;
        self::$cachedUserId = null;
        self::$cachedAdminToken = null;
        parent::tearDownAfterClass();
    }
}
