<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * This controller exists only to expose the JWT login endpoint in the OpenAPI docs.
 * The actual authentication is handled by LexikJWTAuthenticationBundle.
 */
#[OA\Tag(name: 'Auth')]
#[Route('/api/auth/login', methods: ['POST'])]
#[OA\Post(
    path: '/api/auth/login',
    summary: 'Obtain a JWT token',
    description: 'Authenticate with email and password.'
        . ' Returns a JWT token to use as `Authorization: Bearer <token>` on all protected endpoints.',
    security: [],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'password'],
            properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email', example: 'owner@example.com'),
                new OA\Property(property: 'password', type: 'string', example: 'secret1234'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'JWT token issued',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(
                        property: 'token',
                        type: 'string',
                        example: 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9...'
                    ),
                ]
            )
        ),
        new OA\Response(response: 401, description: 'Invalid credentials'),
    ]
)]
final class LoginDocController
{
    public function __invoke(): JsonResponse
    {
        // Handled by LexikJWTAuthenticationBundle — this method is never reached.
        return new JsonResponse(['error' => 'Unauthorized.'], Response::HTTP_UNAUTHORIZED);
    }
}
