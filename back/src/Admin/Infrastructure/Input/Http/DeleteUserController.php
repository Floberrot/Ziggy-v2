<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Application\Command\DeleteUser\DeleteUserCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/users/{userId}', methods: ['DELETE'])]
final readonly class DeleteUserController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $userId): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteUserCommand($userId));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
