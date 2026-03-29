<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Infrastructure\Output\Persistence\UserOrmEntity;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Auth')]
#[Route('/api/auth/me', methods: ['GET'])]
#[OA\Get(
    path: '/api/auth/me',
    summary: 'Get the authenticated user profile',
    responses: [
        new OA\Response(
            response: 200,
            description: 'Current user',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'id', type: 'string'),
                new OA\Property(property: 'email', type: 'string'),
                new OA\Property(property: 'role', type: 'string'),
                new OA\Property(property: 'username', type: 'string', nullable: true),
            ])
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
final readonly class MeController
{
    public function __construct(private Security $security)
    {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser();
        \assert($user instanceof UserOrmEntity);

        return new JsonResponse([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'role' => $user->getRole(),
            'username' => $user->getUsername(),
        ]);
    }
}
