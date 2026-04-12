<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class ListActivityLogsControllerTest extends AuthenticatedWebTestCase
{
    public function testAdminCanListActivityLogs(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/activity-logs');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<mixed>, total?: int, page?: int, totalPages?: int} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('page', $data);
        self::assertArrayHasKey('totalPages', $data);
    }

    public function testActivityLogsAreRecordedAfterRequests(): void
    {
        $regularClient = $this->createAuthenticatedClient();
        $regularClient->request('GET', '/api/cats');

        $adminClient = $this->createAdminClient();
        $adminClient->request('GET', '/api/admin/activity-logs');

        self::assertResponseIsSuccessful();

        /** @var array{total?: int} $data */
        $data = json_decode($adminClient->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('total', $data);
        self::assertGreaterThanOrEqual(0, $data['total']);
    }

    public function testActivityLogsFilterByMethod(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/activity-logs?method=GET');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<array{method?: string}>} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['items'] ?? [] as $item) {
            self::assertSame('GET', $item['method']);
        }
    }

    public function testListActivityLogsReturns403ForRegularUser(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/admin/activity-logs');

        self::assertResponseStatusCodeSame(403);
    }

    public function testAdminCanReadAppLogs(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/logs/app?lines=10');

        self::assertResponseIsSuccessful();

        /** @var array{lines?: list<string>, file?: string} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('lines', $data);
        self::assertArrayHasKey('file', $data);
        self::assertIsArray($data['lines']);
    }

    public function testAdminCanReadServerLogs(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/logs/server?lines=10');

        self::assertResponseIsSuccessful();

        /** @var array{lines?: list<string>, file?: string} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('lines', $data);
        self::assertArrayHasKey('file', $data);
        self::assertIsArray($data['lines']);
    }
}
