<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Tests\Functional\AuthenticatedWebTestCase;

final class DeleteUserControllerTest extends AuthenticatedWebTestCase
{
    public function testAdminCanDeleteUser(): void
    {
        // Create a user to delete
        $regularClient = static::createClient();
        $regularClient->request('POST', '/api/auth/register', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'email' => 'todelete@functional.test',
            'password' => 'DeleteMe1!',
            'username' => 'todelete',
        ], JSON_THROW_ON_ERROR));

        // Fetch admin users list to find the ID
        $adminClient = $this->createAdminClient();
        $adminClient->request('GET', '/api/admin/users');

        /** @var array{items?: list<array{id: string, email: string}>} $data */
        $data = json_decode($adminClient->getResponse()->getContent() ?: '{}', true, 512, JSON_THROW_ON_ERROR);

        $userId = null;
        foreach ($data['items'] ?? [] as $item) {
            if ($item['email'] === 'todelete@functional.test') {
                $userId = $item['id'];
                break;
            }
        }

        self::assertNotNull($userId, 'User to delete not found in list.');

        $adminClient->request('DELETE', '/api/admin/users/' . $userId);

        self::assertResponseStatusCodeSame(204);
    }

    public function testDeleteNonExistentUserReturns404(): void
    {
        $client = $this->createAdminClient();

        $client->request('DELETE', '/api/admin/users/00000000-0000-0000-0000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeleteUserReturns403ForRegularUser(): void
    {
        $client = $this->createAuthenticatedClient();

        $client->request('DELETE', '/api/admin/users/some-id');

        self::assertResponseStatusCodeSame(403);
    }
}
