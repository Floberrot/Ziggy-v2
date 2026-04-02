<?php

declare(strict_types=1);

namespace App\Tests\Functional\Identity;

use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Invitation;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Tests\Functional\AuthenticatedWebTestCase;
use Symfony\Component\Uid\Uuid;

final class AcceptInvitationControllerTest extends AuthenticatedWebTestCase
{
    public function testAcceptInvitationReturns201WithValidToken(): void
    {
        $client = $this->createAuthenticatedClient();
        $container = static::getContainer();

        /** @var InvitationRepository $invitationRepo */
        $invitationRepo = $container->get(InvitationRepository::class);

        $invitation = Invitation::create(
            id: (string) Uuid::v4(),
            ownerId: new UserId((string) Uuid::v4()),
            inviteeEmail: new Email('petsitter-func@test.com'),
            catId: 'cat-func-123',
        );
        $invitationRepo->save($invitation);

        $client->request('POST', '/api/auth/invitation/accept', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'token' => $invitation->token(),
            'password' => 'PetSitterPass1!',
            'username' => 'petsitterfunc',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
    }

    public function testAcceptInvitationReturns400WithInvalidToken(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/invitation/accept', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'token' => 'totally-invalid-token',
            'password' => 'SomePass1!',
            'username' => 'nobody',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(404);
    }

    public function testAcceptInvitationReturns422WithMissingFields(): void
    {
        $client = static::createClient();

        $client->request('POST', '/api/auth/invitation/accept', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'token' => 'some-token',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
    }
}
