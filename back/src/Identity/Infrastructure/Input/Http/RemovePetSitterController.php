<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\RemovePetSitter\RemovePetSitterCommand;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pet-sitters/{id}', methods: ['DELETE'])]
final readonly class RemovePetSitterController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $this->commandBus->dispatch(new RemovePetSitterCommand(
            petSitterId: $id,
            ownerEmail: $user->getUserIdentifier(),
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
