<?php

declare(strict_types=1);

namespace App\Tests\Functional\Cat;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class RemoveCatControllerTest extends AuthenticatedWebTestCase
{
    public function testRemoveCatReturns204(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'CatToRemove'], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);

        /** @var array{id: string} $data */
        $data = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $catId = $data['id'];

        $client->request('DELETE', '/api/cats/' . $catId);

        self::assertResponseStatusCodeSame(204);
    }

    public function testRemoveCatReturns404WhenNotFound(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/api/cats/00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }

    public function testRemoveCatReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/cats/some-id');

        self::assertResponseStatusCodeSame(401);
    }
}
