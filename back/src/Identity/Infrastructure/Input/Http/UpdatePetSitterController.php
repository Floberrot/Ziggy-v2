<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\UpdatePetSitter\UpdatePetSitterCommand;
use App\Identity\Infrastructure\Input\Http\Request\UpdatePetSitterRequest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pet-sitters/{id}', methods: ['PUT'])]
final readonly class UpdatePetSitterController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(string $id, #[MapRequestPayload] UpdatePetSitterRequest $request): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new UpdatePetSitterCommand(
            petSitterId: $id,
            ownerEmail: $user->getUserIdentifier(),
            type: $request->type,
            age: $request->age,
            phoneNumber: $request->phoneNumber,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
