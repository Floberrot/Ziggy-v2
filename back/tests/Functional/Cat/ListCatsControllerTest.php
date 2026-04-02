<?php

declare(strict_types=1);

namespace App\Tests\Functional\Cat;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class ListCatsControllerTest extends AuthenticatedWebTestCase
{
    public function testListCatsReturnsArray(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/cats');

        self::assertResponseIsSuccessful();

        /** @var list<mixed> $data */
        $data = json_decode(
            $client->getResponse()->getContent() ?: '[]',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($data);
    }

    public function testListCatsReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/cats');

        self::assertResponseStatusCodeSame(401);
    }

    public function testListCatsContainsAddedCat(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'ListTestCat'], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);

        $client->request('GET', '/api/cats');

        self::assertResponseIsSuccessful();

        $body = $client->getResponse()->getContent() ?: '[]';
        self::assertStringContainsString('ListTestCat', $body);
    }
}
