<?php

declare(strict_types=1);

namespace App\Tests\Functional\Calendar;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class PlaceChipControllerTest extends AuthenticatedWebTestCase
{
    public function testPlaceChipReturns201(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'CalendarCat'], JSON_THROW_ON_ERROR));

        /** @var array{id: string} $catData */
        $catData = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $catId = $catData['id'];

        $client->request(
            'POST',
            '/api/cats/' . $catId . '/chips',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'chipTypeId' => '00000000-0000-0000-0000-000000000001',
                'dateTime' => '2026-03-15T00:00:00+00:00',
                'note' => 'Test chip',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
    }

    public function testPlaceChipReturns422WithMissingFields(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/cats/some-cat-id/chips',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['note' => 'Missing required fields'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testPlaceChipReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/cats/some-cat-id/chips',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'chipTypeId' => '00000000-0000-0000-0000-000000000001',
                'dateTime' => '2026-03-15T00:00:00+00:00',
            ], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(401);
    }
}
