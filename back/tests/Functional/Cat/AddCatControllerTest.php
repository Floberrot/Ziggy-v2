<?php

declare(strict_types=1);

namespace App\Tests\Functional\Cat;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class AddCatControllerTest extends AuthenticatedWebTestCase
{
    public function testAddCatReturns201WithValidPayload(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Ziggy',
            'weight' => 4.2,
            'breed' => 'Tabby',
            'colors' => ['#ff6600'],
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);

        /** @var array{id?: string} $data */
        $data = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('id', $data);
        self::assertNotEmpty($data['id']);
    }

    public function testAddCatReturns422WithMissingName(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'weight' => 4.2,
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
    }

    public function testAddCatReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'Luna'], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(401);
    }
}
