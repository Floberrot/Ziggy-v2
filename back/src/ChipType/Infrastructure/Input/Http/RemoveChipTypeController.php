<?php

declare(strict_types=1);

namespace App\ChipType\Infrastructure\Input\Http;

use App\ChipType\Application\Command\RemoveChipType\RemoveChipTypeCommand;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'ChipTypes')]
#[Route('/api/chip-types/{id}', methods: ['DELETE'])]
#[OA\Delete(
    path: '/api/chip-types/{id}',
    summary: 'Remove a chip type',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    responses: [
        new OA\Response(response: 204, description: 'Removed'),
        new OA\Response(response: 404, description: 'Chip type not found'),
    ]
)]
final readonly class RemoveChipTypeController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new RemoveChipTypeCommand(
            chipTypeId: $id,
            requestingUserId: $user->getUserIdentifier(),
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
