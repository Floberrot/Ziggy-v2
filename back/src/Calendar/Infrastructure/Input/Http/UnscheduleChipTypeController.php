<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Input\Http;

use App\Calendar\Application\Command\UnscheduleChipType\UnscheduleChipTypeCommand;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Calendar')]
#[Route('/api/cats/{catId}/calendar/scheduled-chip-types/{chipTypeId}', methods: ['DELETE'])]
#[OA\Delete(
    path: '/api/cats/{catId}/calendar/scheduled-chip-types/{chipTypeId}',
    summary: 'Unschedule a chip type from a cat calendar',
    parameters: [
        new OA\Parameter(name: 'catId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        new OA\Parameter(name: 'chipTypeId', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
    ],
    responses: [
        new OA\Response(response: 204, description: 'Chip type unscheduled'),
        new OA\Response(response: 404, description: 'Calendar or scheduled chip type not found'),
    ]
)]
final readonly class UnscheduleChipTypeController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $catId, string $chipTypeId): JsonResponse
    {
        $this->commandBus->dispatch(new UnscheduleChipTypeCommand(
            catId: $catId,
            chipTypeId: $chipTypeId,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
