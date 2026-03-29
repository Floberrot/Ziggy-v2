<?php

declare(strict_types=1);

namespace App\Calendar\Infrastructure\Input\Http;

use App\Calendar\Application\Command\PlaceChip\PlaceChipCommand;
use App\Calendar\Infrastructure\Input\Http\Request\PlaceChipRequest;
use App\Identity\Infrastructure\Output\Persistence\UserOrmEntity;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Calendar')]
#[Route('/api/cats/{catId}/chips', methods: ['POST'])]
#[OA\Post(
    path: '/api/cats/{catId}/chips',
    summary: 'Place a chip on a cat\'s calendar',
    description: 'Places a chip (task token) on a specific day of a cat\'s calendar. Creates the calendar automatically if it does not yet exist.',
    parameters: [
        new OA\Parameter(name: 'catId', in: 'path', required: true, schema: new OA\Schema(type: 'string', format: 'uuid')),
    ],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['chipTypeId', 'date'],
            properties: [
                new OA\Property(property: 'chipTypeId', type: 'string', format: 'uuid', example: 'a1b2c3d4-...'),
                new OA\Property(property: 'date', type: 'string', format: 'date', example: '2026-03-28'),
                new OA\Property(property: 'note', type: 'string', nullable: true, example: 'Annual checkup'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 201, description: 'Chip placed successfully'),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class PlaceChipController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] PlaceChipRequest $request, string $catId): JsonResponse
    {
        $user = $this->security->getUser();
        \assert($user instanceof UserOrmEntity);

        $this->commandBus->dispatch(new PlaceChipCommand(
            catId: $catId,
            chipTypeId: $request->chipTypeId,
            date: $request->date,
            note: $request->note,
            authorId: $user->getId(),
        ));

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
