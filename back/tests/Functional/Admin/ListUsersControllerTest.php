<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class ListUsersControllerTest extends AuthenticatedWebTestCase
{
    public function testListUsersReturnsPagedResult(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/users');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<mixed>, total?: int, page?: int, limit?: int, totalPages?: int} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('total', $data);
        self::assertArrayHasKey('page', $data);
        self::assertArrayHasKey('totalPages', $data);
        self::assertIsArray($data['items']);
    }

    public function testListUsersReturns401WithoutToken(): void
    {
        $client = static::createClient();

        $client->request('GET', '/api/admin/users');

        self::assertResponseStatusCodeSame(401);
    }

    public function testListUsersReturns403ForRegularUser(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/admin/users');

        self::assertResponseStatusCodeSame(403);
    }

    public function testListUsersPaginationWorks(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/users?page=1&limit=2');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<mixed>, limit?: int} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertLessThanOrEqual(2, count($data['items'] ?? []));
    }
}
