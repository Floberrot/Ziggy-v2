<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\RegisterOwner\RegisterOwnerCommand;
use App\Identity\Infrastructure\Input\Http\Request\RegisterOwnerRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Auth')]
#[Route('/api/auth/register', methods: ['POST'])]
#[OA\Post(
    path: '/api/auth/register',
    summary: 'Register a new owner account',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password', 'username'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'owner@example.com'),
                new OA\Property(property: 'password', type: 'string', minLength: 8, example: 'secret1234'),
                new OA\Property(property: 'username', type: 'string', minLength: 2, maxLength: 50, example: 'johndoe'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Owner registered successfully'),
        new OA\Response(
            response: 409,
            description: 'Email already registered',
            content: new OA\JsonContent(properties: [new OA\Property(property: 'error', type: 'string')])
        ),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class RegisterOwnerController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(#[MapRequestPayload] RegisterOwnerRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new RegisterOwnerCommand(
            email: $request->email,
            plainPassword: $request->password,
            username: $request->username,
        ));

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
