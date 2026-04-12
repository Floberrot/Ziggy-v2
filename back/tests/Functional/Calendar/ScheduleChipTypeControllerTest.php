<?php

declare(strict_types=1);

namespace App\Tests\Functional\Calendar;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class ScheduleChipTypeControllerTest extends AuthenticatedWebTestCase
{
    public function testScheduleChipTypeReturns201(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'ScheduleCat'], JSON_THROW_ON_ERROR));

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
            '/api/cats/' . $catId . '/calendar/scheduled-chip-types',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['chipTypeId' => '00000000-0000-0000-0000-000000000001'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(201);
    }

    public function testScheduleChipTypeAppearsInCalendarResponse(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'ScheduleCalendarCat'], JSON_THROW_ON_ERROR));

        /** @var array{id: string} $catData */
        $catData = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $catId = $catData['id'];
        $chipTypeId = '00000000-0000-0000-0000-000000000002';

        $client->request(
            'POST',
            '/api/cats/' . $catId . '/calendar/scheduled-chip-types',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['chipTypeId' => $chipTypeId], JSON_THROW_ON_ERROR)
        );

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
        self::assertContains($chipTypeId, $data['scheduledChipTypeIds']);
    }

    public function testScheduleChipTypeTwiceReturns409(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('POST', '/api/cats', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => 'ScheduleDupCat'], JSON_THROW_ON_ERROR));

        /** @var array{id: string} $catData */
        $catData = json_decode(
            $client->getResponse()->getContent() ?: '{}',
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $catId = $catData['id'];
        $payload = json_encode(['chipTypeId' => '00000000-0000-0000-0000-000000000003'], JSON_THROW_ON_ERROR);

        $scheduleUrl = '/api/cats/' . $catId . '/calendar/scheduled-chip-types';
        $client->request('POST', $scheduleUrl, [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::assertResponseStatusCodeSame(201);

        $client->request('POST', $scheduleUrl, [], [], ['CONTENT_TYPE' => 'application/json'], $payload);
        self::assertResponseStatusCodeSame(409);
    }

    public function testScheduleChipTypeReturns422OnMissingBody(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request(
            'POST',
            '/api/cats/00000000-0000-0000-0000-000000000099/calendar/scheduled-chip-types',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['chipTypeId' => ''], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(422);
    }

    public function testScheduleChipTypeReturns401WhenNotAuthenticated(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/cats/some-id/calendar/scheduled-chip-types',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['chipTypeId' => 'some-type'], JSON_THROW_ON_ERROR)
        );

        self::assertResponseStatusCodeSame(401);
    }
}
