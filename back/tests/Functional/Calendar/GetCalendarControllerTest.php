<?php

declare(strict_types=1);

namespace App\Tests\Functional\Calendar;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class GetCalendarControllerTest extends AuthenticatedWebTestCase
{
    public function testGetCalendarReturns200AfterChipIsPlaced(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'CalendarViewCat'], JSON_THROW_ON_ERROR));

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
            ], JSON_THROW_ON_ERROR)
        );

        $client->request('GET', '/api/cats/' . $catId . '/calendar');

        self::assertResponseIsSuccessful();

        /** @var array{id?: string, chips?: list<mixed>} $data */
        $data = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('chips', $data);
        self::assertCount(1, $data['chips']);
    }

    public function testGetCalendarReturns404WhenNoChipPlaced(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/cats/00000000-0000-0000-0000-000000000099/calendar');

        self::assertResponseStatusCodeSame(404);
    }

    public function testGetCalendarReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/cats/some-cat-id/calendar');

        self::assertResponseStatusCodeSame(401);
    }
}
