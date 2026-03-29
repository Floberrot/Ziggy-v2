<?php

declare(strict_types=1);

namespace App\ChipType\Infrastructure\Input\Http;

use App\ChipType\Application\Command\CreateChipType\CreateChipTypeCommand;
use App\ChipType\Infrastructure\Input\Http\Request\CreateChipTypeRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Chip Types')]
#[Route('/api/chip-types', methods: ['POST'])]
#[OA\Post(
    path: '/api/chip-types',
    summary: 'Create a new chip type for the authenticated owner',
    description: 'Chip types define task categories (e.g. Feeding, Vet visit) with a custom hex color.',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'color'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Vet visit'),
                new OA\Property(property: 'color', type: 'string', description: 'Hex color code', example: '#f97316'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Chip type created',
            content: new OA\JsonContent(properties: [new OA\Property(property: 'id', type: 'string', format: 'uuid')])
        ),
        new OA\Response(
            response: 400,
            description: 'Invalid hex color',
            content: new OA\JsonContent(properties: [new OA\Property(property: 'error', type: 'string')])
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class CreateChipTypeController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateChipTypeRequest $request): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->commandBus->dispatch(new CreateChipTypeCommand(
            ownerId: $user->getUserIdentifier(),
            name: $request->name,
            color: $request->color,
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        return new JsonResponse(['id' => $stamp->getResult()], Response::HTTP_CREATED);
    }
}
