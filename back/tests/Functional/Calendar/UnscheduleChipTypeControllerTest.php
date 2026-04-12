<?php

declare(strict_types=1);

namespace App\Tests\Functional\Calendar;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class UnscheduleChipTypeControllerTest extends AuthenticatedWebTestCase
{
    public function testUnscheduleChipTypeReturns204(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'UnscheduleCat'], JSON_THROW_ON_ERROR));

        /** @var array{id: string} $catData */
        $catData = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $catId = $catData['id'];
        $chipTypeId = '00000000-0000-0000-0000-000000000004';

        $client->request(
            'POST',
            '/api/cats/' . $catId . '/calendar/scheduled-chip-types',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['chipTypeId' => $chipTypeId], JSON_THROW_ON_ERROR)
        );

        $client->request(
            'DELETE',
            '/api/cats/' . $catId . '/calendar/scheduled-chip-types/' . $chipTypeId
        );

        self::assertResponseStatusCodeSame(204);
    }

    public function testUnscheduleRemovedFromCalendarResponse(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'UnscheduleCalendarCat'], JSON_THROW_ON_ERROR));

        /** @var array{id: string} $catData */
        $catData = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $catId = $catData['id'];
        $chipTypeId = '00000000-0000-0000-0000-000000000005';

        $scheduleUrl = '/api/cats/' . $catId . '/calendar/scheduled-chip-types';
        $body = json_encode(['chipTypeId' => $chipTypeId], JSON_THROW_ON_ERROR);
        $client->request('POST', $scheduleUrl, [], [], ['CONTENT_TYPE' => 'application/json'], $body);
        $client->request('DELETE', '/api/cats/' . $catId . '/calendar/scheduled-chip-types/' . $chipTypeId);

        $client->request('GET', '/api/cats/' . $catId . '/calendar');

        self::assertResponseIsSuccessful();

        /** @var array{scheduledChipTypeIds?: list<string>} $data */
        $data = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        self::assertArrayHasKey('scheduledChipTypeIds', $data);
        self::assertNotContains($chipTypeId, $data['scheduledChipTypeIds']);
    }

    public function testUnscheduleNonExistentCalendarReturns404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'DELETE',
            '/api/cats/00000000-0000-0000-0000-000000999999/calendar/scheduled-chip-types/some-type'
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testUnscheduleNotScheduledChipTypeReturns404(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'UnscheduleNotFoundCat'], JSON_THROW_ON_ERROR));

        /** @var array{id: string} $catData */
        $catData = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $catId = $catData['id'];

        $scheduleUrl = '/api/cats/' . $catId . '/calendar/scheduled-chip-types';
        $chipId = '00000000-0000-0000-0000-000000000006';
        $body = json_encode(['chipTypeId' => $chipId], JSON_THROW_ON_ERROR);
        $client->request('POST', $scheduleUrl, [], [], ['CONTENT_TYPE' => 'application/json'], $body);

        $deleteUrl = '/api/cats/' . $catId . '/calendar/scheduled-chip-types/00000000-0000-0000-0000-different';
        $client->request('DELETE', $deleteUrl);

        self::assertResponseStatusCodeSame(404);
    }

    public function testUnscheduleReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request('DELETE', '/api/cats/some-id/calendar/scheduled-chip-types/some-type');

        self::assertResponseStatusCodeSame(401);
    }
}
