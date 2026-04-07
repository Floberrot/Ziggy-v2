<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Application\Command\UpdateUser\UpdateUserAdminCommand;
use App\Admin\Infrastructure\Input\Http\Request\UpdateUserRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/users/{userId}', methods: ['PATCH'])]
final readonly class UpdateUserController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $userId, #[MapRequestPayload] UpdateUserRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new UpdateUserAdminCommand(
            userId: $userId,
            username: $request->username,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
