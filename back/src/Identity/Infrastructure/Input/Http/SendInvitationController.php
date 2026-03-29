<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Command\SendInvitation\SendInvitationCommand;
use App\Identity\Infrastructure\Input\Http\Request\SendInvitationRequest;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Invitations')]
#[Route('/api/invitations', methods: ['POST'])]
#[OA\Post(
    path: '/api/invitations',
    summary: 'Send an invitation to a pet sitter',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['inviteeEmail', 'catId'],
            properties: [
                new OA\Property(property: 'inviteeEmail', type: 'string', format: 'email'),
                new OA\Property(property: 'catId', type: 'string', format: 'uuid'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Invitation sent',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'token', type: 'string'),
            ])
        ),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
final readonly class SendInvitationController
{
    public function __construct(
        private MessageBusInterface $commandBus,
        private Security $security,
    ) {
    }

    public function __invoke(#[MapRequestPayload] SendInvitationRequest $request): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->commandBus->dispatch(new SendInvitationCommand(
            ownerEmail: $user->getUserIdentifier(),
            inviteeEmail: $request->inviteeEmail,
            catId: $request->catId,
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        return new JsonResponse(['token' => $stamp->getResult()], Response::HTTP_CREATED);
    }
}
