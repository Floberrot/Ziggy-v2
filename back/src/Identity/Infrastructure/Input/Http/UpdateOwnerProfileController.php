<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\UpdateOwnerProfile\UpdateOwnerProfileCommand;
use App\Identity\Infrastructure\Input\Http\Request\UpdateOwnerProfileRequest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/profile', methods: ['PUT'])]
final readonly class UpdateOwnerProfileController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] UpdateOwnerProfileRequest $request): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new UpdateOwnerProfileCommand(
            ownerEmail: $user->getUserIdentifier(),
            age: $request->age,
            phoneNumber: $request->phoneNumber,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
