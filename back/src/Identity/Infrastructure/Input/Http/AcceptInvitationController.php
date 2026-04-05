<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\AcceptInvitation\AcceptInvitationCommand;
use App\Identity\Application\Command\AcceptInvitation\AcceptInvitationResult;
use App\Identity\Application\Port\TokenGenerator;
use App\Identity\Infrastructure\Input\Http\Request\AcceptInvitationRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
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
            required: ['token', 'password', 'username'],
            properties: [
                new OA\Property(property: 'token', type: 'string', description: 'Invitation token from email'),
                new OA\Property(property: 'password', type: 'string', minLength: 8),
                new OA\Property(property: 'username', type: 'string', minLength: 2, maxLength: 50, example: 'johndoe'),
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
    public function __construct(
        private MessageBusInterface $commandBus,
        private TokenGenerator $tokenGenerator,
    ) {
    }

    public function __invoke(#[MapRequestPayload] AcceptInvitationRequest $request): JsonResponse
    {
        $envelope = $this->commandBus->dispatch(
            new AcceptInvitationCommand($request->token, $request->password, $request->username),
        );

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var AcceptInvitationResult $result */
        $result = $stamp->getResult();

        $token = $this->tokenGenerator->generateForEmail($result->email);

        return new JsonResponse(['token' => $token], Response::HTTP_CREATED);
    }
}
