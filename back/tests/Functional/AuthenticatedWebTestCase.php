<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    private static ?string $cachedToken = null;
    private static ?string $cachedUserId = null;
    private static string $testEmail = 'testowner@functional.test';
    private static string $testPassword = 'FunctionalPass1!';
    private static string $testUsername = 'functionalowner';

    protected function createAuthenticatedClient(): KernelBrowser
    {
        $client = static::createClient();

        $token = $this->getAuthToken($client);

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

    protected function getTestEmail(): string
    {
        return self::$testEmail;
    }

    public static function tearDownAfterClass(): void
    {
        self::$cachedToken = null;
        self::$cachedUserId = null;
        parent::tearDownAfterClass();
    }
}
