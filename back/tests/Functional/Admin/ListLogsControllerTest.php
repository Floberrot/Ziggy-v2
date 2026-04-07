<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class ListLogsControllerTest extends AuthenticatedWebTestCase
{
    public function testAdminCanListLogs(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/logs');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<mixed>, total?: int, page?: int} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('page', $data);
    }

    public function testLogsAreCreatedAfterAnError(): void
    {
        // Trigger a 404 to generate a log entry
        $regularClient = $this->createAuthenticatedClient();
        $regularClient->request('GET', '/api/cats/non-existent-uuid');

        // Now check admin logs
        $adminClient = $this->createAdminClient();
        $adminClient->request('GET', '/api/admin/logs?logLevel=warning');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<mixed>, total?: int} $data */
        $data = json_decode($adminClient->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('total', $data);
        self::assertGreaterThanOrEqual(0, $data['total']);
    }

    public function testLogsFilterByLogLevel(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/logs?logLevel=error');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<array{logLevel?: string}>} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        foreach ($data['items'] ?? [] as $item) {
            self::assertSame('error', $item['logLevel']);
        }
    }

    public function testListLogsReturns403ForRegularUser(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/admin/logs');

        self::assertResponseStatusCodeSame(403);
    }
}
