<?php

declare(strict_types=1);

namespace App\Tests\Integration\Identity\Application\Command;

use App\Identity\Application\Command\AcceptInvitation\AcceptInvitationCommand;
use App\Identity\Application\Command\AcceptInvitation\AcceptInvitationHandler;
use App\Identity\Application\Command\RegisterOwner\RegisterOwnerCommand;
use App\Identity\Application\Command\RegisterOwner\RegisterOwnerHandler;
use App\Identity\Domain\Exception\InvitationNotFoundException;
use App\Identity\Domain\Model\Email;
use App\Identity\Domain\Model\Invitation;
use App\Identity\Domain\Model\UserId;
use App\Identity\Domain\Repository\InvitationRepository;
use App\Identity\Domain\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class AcceptInvitationHandlerTest extends KernelTestCase
{
    private AcceptInvitationHandler $handler;
    private RegisterOwnerHandler $registerHandler;
    private InvitationRepository $invitationRepository;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->handler = $container->get(AcceptInvitationHandler::class);
        $this->registerHandler = $container->get(RegisterOwnerHandler::class);
        $this->invitationRepository = $container->get(InvitationRepository::class);
        $this->userRepository = $container->get(UserRepository::class);
    }

    public function testAcceptsInvitationAndCreatesPetSitter(): void
    {
        ($this->registerHandler)(new RegisterOwnerCommand(
            email: 'owner@test.com',
            plainPassword: 'OwnerPass1!',
            username: 'owner',
        ));

        $owner = $this->userRepository->findByEmail(new Email('owner@test.com'));
        self::assertNotNull($owner);

        $invitation = Invitation::create(
            id: (string) \Symfony\Component\Uid\Uuid::v4(),
            ownerId: $owner->id(),
            inviteeEmail: new Email('petsitter@test.com'),
            catId: 'cat-123',
        );
        $this->invitationRepository->save($invitation);

        ($this->handler)(new AcceptInvitationCommand(
            token: $invitation->token(),
            plainPassword: 'PetSitterPass1!',
            username: 'petsitter',
        ));

        $petSitter = $this->userRepository->findByEmail(new Email('petsitter@test.com'));
        self::assertNotNull($petSitter);
        self::assertSame('petsitter', $petSitter->username());

        $savedInvitation = $this->invitationRepository->findByToken($invitation->token());
        self::assertNotNull($savedInvitation);
        self::assertTrue($savedInvitation->isAccepted());
    }

    public function testThrowsWhenTokenNotFound(): void
    {
        $this->expectException(InvitationNotFoundException::class);

        ($this->handler)(new AcceptInvitationCommand(
            token: 'invalid-token',
            plainPassword: 'Pass1!',
            username: 'nobody',
        ));
    }
}
