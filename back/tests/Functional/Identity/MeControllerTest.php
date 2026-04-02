<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class MeControllerTest extends AuthenticatedWebTestCase
{
    public function testMeReturnsAuthenticatedUserData(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/auth/me');

        self::assertResponseIsSuccessful();

        /** @var array{email?: string, username?: string} $data */
        $data = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertSame($this->getTestEmail(), $data['email'] ?? null);
    }

    public function testMeReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/auth/me');

        self::assertResponseStatusCodeSame(401);
    }
}
