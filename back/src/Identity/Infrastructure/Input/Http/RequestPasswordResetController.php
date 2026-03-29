<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\RequestPasswordReset\RequestPasswordResetCommand;
use App\Identity\Infrastructure\Input\Http\Request\RequestPasswordResetRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Auth')]
#[Route('/api/auth/password-reset/request', methods: ['POST'])]
#[OA\Post(
    path: '/api/auth/password-reset/request',
    summary: 'Request a password reset link',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email'],
            properties: [new OA\Property(property: 'email', type: 'string', format: 'email')]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Always succeeds to prevent email enumeration',
            content: new OA\JsonContent(properties: [new OA\Property(property: 'message', type: 'string')])
        ),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class RequestPasswordResetController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(#[MapRequestPayload] RequestPasswordResetRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new RequestPasswordResetCommand($request->email));

        return new JsonResponse(['message' => 'If this email exists, a reset link has been sent.']);
    }
}
