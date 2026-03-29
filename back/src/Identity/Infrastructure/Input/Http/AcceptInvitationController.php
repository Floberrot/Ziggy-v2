<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\AcceptInvitation\AcceptInvitationCommand;
use App\Identity\Infrastructure\Input\Http\Request\AcceptInvitationRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Auth')]
#[Route('/api/auth/invitation/accept', methods: ['POST'])]
#[OA\Post(
    path: '/api/auth/invitation/accept',
    summary: 'Accept an invitation and create a pet sitter account',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['token', 'password'],
            properties: [
                new OA\Property(property: 'token', type: 'string', description: 'Invitation token from email'),
                new OA\Property(property: 'password', type: 'string', minLength: 8),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Account created — pet sitter can now log in'),
        new OA\Response(
            response: 400,
            description: 'Invalid or expired token',
            content: new OA\JsonContent(properties: [new OA\Property(property: 'error', type: 'string')])
        ),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class AcceptInvitationController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(#[MapRequestPayload] AcceptInvitationRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new AcceptInvitationCommand($request->token, $request->password));

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
