<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Application\Command\DeletePetSitter\DeletePetSitterAdminCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/pet-sitters/{petSitterId}', methods: ['DELETE'])]
final readonly class DeletePetSitterAdminController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $petSitterId): JsonResponse
    {
        $this->commandBus->dispatch(new DeletePetSitterAdminCommand($petSitterId));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
