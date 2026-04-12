<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Input\Http;

use App\Calendar\Application\Command\ScheduleChipType\ScheduleChipTypeCommand;
use App\Calendar\Infrastructure\Input\Http\Request\ScheduleChipTypeRequest;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Calendar')]
#[Route('/api/cats/{catId}/calendar/scheduled-chip-types', methods: ['POST'])]
#[OA\Post(
    path: '/api/cats/{catId}/calendar/scheduled-chip-types',
    summary: 'Schedule a chip type for a cat calendar',
    description: 'Marks a chip type as scheduled for a cat\'s calendar.'
        . ' Scheduled chip types appear as daily to-do checkboxes in the calendar.',
    parameters: [
        new OA\Parameter(
            name: 'catId',
            in: 'path',
            required: true,
            schema: new OA\Schema(type: 'string', format: 'uuid')
        ),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['chipTypeId'],
            properties: [
                new OA\Property(property: 'chipTypeId', type: 'string', format: 'uuid'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Chip type scheduled'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 409, description: 'Chip type already scheduled'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class ScheduleChipTypeController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(#[MapRequestPayload] ScheduleChipTypeRequest $request, string $catId): JsonResponse
    {
        $this->commandBus->dispatch(new ScheduleChipTypeCommand(
            catId: $catId,
            chipTypeId: $request->chipTypeId,
        ));

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
