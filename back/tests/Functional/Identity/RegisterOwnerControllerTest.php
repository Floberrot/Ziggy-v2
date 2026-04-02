<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class RegisterOwnerControllerTest extends WebTestCase
{
    public function testRegisterReturns201WithValidPayload(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'newowner@test.com',
            'password' => 'SecurePass1!',
            'username' => 'newowner',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
    }

    public function testRegisterReturns422WithMissingEmail(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'password' => 'SecurePass1!',
            'username' => 'newowner',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
    }

    public function testRegisterReturns409WhenEmailAlreadyTaken(): void
    {
        $client = static::createClient();

        $payload = json_encode([
            'email' => 'duplicate@test.com',
            'password' => 'SecurePass1!',
            'username' => 'duplicate',
        ], JSON_THROW_ON_ERROR);

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        self::assertResponseStatusCodeSame(201);

        $client->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], $payload);

        self::assertResponseStatusCodeSame(409);
    }
}
