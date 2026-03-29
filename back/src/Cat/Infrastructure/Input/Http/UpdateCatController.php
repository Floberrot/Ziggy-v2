<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Input\Http;

use App\Cat\Application\Command\UpdateCat\UpdateCatCommand;
use App\Cat\Infrastructure\Input\Http\Request\UpdateCatRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Cats')]
#[Route('/api/cats/{id}', methods: ['PUT'])]
#[OA\Put(
    path: '/api/cats/{id}',
    summary: 'Update a cat',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'weight', type: 'number', nullable: true),
                new OA\Property(property: 'breed', type: 'string', nullable: true),
                new OA\Property(property: 'colors', type: 'array', items: new OA\Items(type: 'string')),
            ]
        )
    ),
    responses: [
        new OA\Response(response: 204, description: 'Updated'),
        new OA\Response(response: 404, description: 'Cat not found'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class UpdateCatController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] UpdateCatRequest $request, string $id): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new UpdateCatCommand(
            catId: $id,
            requestingUserId: $user->getUserIdentifier(),
            name: $request->name,
            weight: $request->weight,
            breed: $request->breed,
            colors: $request->colors,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
