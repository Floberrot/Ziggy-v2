<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\DeclineInvitation\DeclineInvitationCommand;
use App\Identity\Infrastructure\Input\Http\Request\DeclineInvitationRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/invitation/decline', methods: ['POST'])]
final readonly class DeclineInvitationController
{
    public function __construct(
        private MessageBusInterface $commandBus,
    ) {
    }

    public function __invoke(#[MapRequestPayload] DeclineInvitationRequest $request): JsonResponse
    {
        $this->commandBus->dispatch(new DeclineInvitationCommand(
            token: $request->token,
        ));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
