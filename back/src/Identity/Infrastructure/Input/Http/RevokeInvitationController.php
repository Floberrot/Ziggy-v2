<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\RevokeInvitation\RevokeInvitationCommand;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Invitations')]
#[Route('/api/invitations/{id}', methods: ['DELETE'])]
#[OA\Delete(
    path: '/api/invitations/{id}',
    summary: 'Revoke an invitation',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    responses: [
        new OA\Response(response: 204, description: 'Revoked'),
        new OA\Response(response: 404, description: 'Invitation not found'),
    ]
)]
final readonly class RevokeInvitationController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new RevokeInvitationCommand(
            invitationId: $id,
            ownerEmail: $user->getUserIdentifier(),
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
