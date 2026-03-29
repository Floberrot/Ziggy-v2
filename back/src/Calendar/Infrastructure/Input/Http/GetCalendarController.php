<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Input\Http;

use App\Calendar\Application\Query\GetCalendar\CalendarView;
use App\Calendar\Application\Query\GetCalendar\GetCalendarQuery;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Calendar')]
#[Route('/api/cats/{catId}/calendar', methods: ['GET'])]
#[OA\Get(
    path: '/api/cats/{catId}/calendar',
    summary: 'Get the calendar for a cat',
    description: 'Returns the full calendar with all chips placed on it for a given cat.',
    parameters: [
        new OA\Parameter(name: 'catId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Calendar with chips',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                    new OA\Property(property: 'catId', type: 'string', format: 'uuid'),
                    new OA\Property(
                        property: 'chips',
                        type: 'array',
                        items: new OA\Items(
                            properties: [
                                new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'chipTypeId', type: 'string', format: 'uuid'),
                                new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-03-28'),
                                new OA\Property(property: 'note', type: 'string', nullable: true),
                            ]
                        )
                    ),
                ]
            )
        ),
        new OA\Response(response: 404, description: 'Calendar not found — no chip has been placed yet'),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
final readonly class GetCalendarController
{
    public function __construct(private MessageBusInterface $queryBus)
    {
    }

    public function __invoke(string $catId): JsonResponse
    {
        $envelope = $this->queryBus->dispatch(new GetCalendarQuery(catId: $catId));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var CalendarView|null $calendar */
        $calendar = $stamp->getResult();

        if (null === $calendar) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($calendar->toArray());
    }
}
