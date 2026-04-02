<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\CreatePetSitter\CreatePetSitterCommand;
use App\Identity\Infrastructure\Input\Http\Request\CreatePetSitterRequest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pet-sitters', methods: ['POST'])]
final readonly class CreatePetSitterController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreatePetSitterRequest $request): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new CreatePetSitterCommand(
            ownerEmail: $user->getUserIdentifier(),
            inviteeEmail: $request->inviteeEmail,
            catId: $request->catId,
            type: $request->type,
            age: $request->age,
            phoneNumber: $request->phoneNumber,
        ));

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
