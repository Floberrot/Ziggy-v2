<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class ListCatsAdminControllerTest extends AuthenticatedWebTestCase
{
    public function testAdminCanListAllCatsWithOwnerInfo(): void
    {
        $client = $this->createAdminClient();

        $client->request('GET', '/api/admin/cats');

        self::assertResponseIsSuccessful();

        /** @var array{items?: list<array{ownerEmail?: string}>, total?: int} $data */
        $data = json_decode($client->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        self::assertArrayHasKey('items', $data);
        self::assertArrayHasKey('total', $data);

        if (!empty($data['items'])) {
            $firstItem = $data['items'][0];
            self::assertArrayHasKey('ownerEmail', $firstItem);
            self::assertArrayHasKey('ownerUsername', $firstItem);
            self::assertArrayHasKey('ownerId', $firstItem);
        }
    }

    public function testListCatsReturns403ForRegularUser(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('GET', '/api/admin/cats');

        self::assertResponseStatusCodeSame(403);
    }
}
