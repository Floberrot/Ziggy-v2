<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\ResetPassword\ResetPasswordCommand;
use App\Identity\Infrastructure\Input\Http\Request\ResetPasswordRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Auth')]
#[Route('/api/auth/password-reset/confirm', methods: ['POST'])]
#[OA\Post(
    path: '/api/auth/password-reset/confirm',
    summary: 'Reset password using a token',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['token', 'password'],
            properties: [
                new OA\Property(property: 'token', type: 'string'),
                new OA\Property(property: 'password', type: 'string', minLength: 8),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 204, description: 'Password reset successfully'),
        new OA\Response(response: 400, description: 'Invalid or expired token'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class ResetPasswordController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(#[MapRequestPayload] ResetPasswordRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new ResetPasswordCommand(
            token: $request->token,
            newPassword: $request->password,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
