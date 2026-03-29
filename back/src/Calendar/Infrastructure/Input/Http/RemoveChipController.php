<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Input\Http;

use App\Calendar\Application\Command\RemoveChip\RemoveChipCommand;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Calendar')]
#[Route('/api/cats/{catId}/chips/{chipId}', methods: ['DELETE'])]
#[OA\Delete(
    path: '/api/cats/{catId}/chips/{chipId}',
    summary: 'Remove a chip from a cat calendar',
    parameters: [
        new OA\Parameter(name: 'catId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'chipId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Chip removed'),
        new OA\Response(response: 404, description: 'Not found'),
    ]
)]
final readonly class RemoveChipController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $catId, string $chipId): JsonResponse
    {
        $this->commandBus->dispatch(new RemoveChipCommand(
            catId: $catId,
            chipId: $chipId,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
