<?php

declare(strict_types=1);

namespace App\ChipType\Infrastructure\Input\Http;

use App\ChipType\Application\Command\UpdateChipType\UpdateChipTypeCommand;
use App\ChipType\Infrastructure\Input\Http\Request\UpdateChipTypeRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'ChipTypes')]
#[Route('/api/chip-types/{id}', methods: ['PUT'])]
#[OA\Put(
    path: '/api/chip-types/{id}',
    summary: 'Update a chip type',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'color'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'color', type: 'string', example: '#FF5733'),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 204, description: 'Updated'),
        new OA\Response(response: 404, description: 'Chip type not found'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class UpdateChipTypeController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] UpdateChipTypeRequest $request, string $id): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new UpdateChipTypeCommand(
            chipTypeId: $id,
            requestingUserId: $user->getUserIdentifier(),
            name: $request->name,
            color: $request->color,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
