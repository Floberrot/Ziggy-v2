<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Input\Http;

use App\Cat\Application\Command\AddCat\AddCatCommand;
use App\Cat\Infrastructure\Input\Http\Request\AddCatRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Cats')]
#[Route('/api/cats', methods: ['POST'])]
#[OA\Post(
    path: '/api/cats',
    summary: 'Add a new cat for the authenticated owner',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'Mochi'),
                new OA\Property(property: 'weight', type: 'number', format: 'float', nullable: true, example: 4.2),
                new OA\Property(property: 'breed', type: 'string', nullable: true, example: 'Maine Coon'),
                new OA\Property(
                    property: 'colors',
                    type: 'array',
                    items: new OA\Items(type: 'string'),
                    example: ['orange', 'white']
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Cat created',
            content: new OA\JsonContent(properties: [new OA\Property(property: 'id', type: 'string', format: 'uuid')])
        ),
        new OA\Response(response: 422, description: 'Validation error'),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
final readonly class AddCatController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] AddCatRequest $request): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->commandBus->dispatch(new AddCatCommand(
            ownerId: $user->getUserIdentifier(),
            name: $request->name,
            weight: $request->weight,
            breed: $request->breed,
            colors: $request->colors,
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        return new JsonResponse(['id' => $stamp->getResult()], Response::HTTP_CREATED);
    }
}
