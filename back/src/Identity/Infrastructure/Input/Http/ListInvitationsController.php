<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Query\ListInvitations\InvitationView;
use App\Identity\Application\Query\ListInvitations\ListInvitationsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Invitations')]
#[Route('/api/invitations', methods: ['GET'])]
#[OA\Get(
    path: '/api/invitations',
    summary: 'List invitations sent by the owner',
    responses: [
        new OA\Response(response: 200, description: 'List of invitations'),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
final readonly class ListInvitationsController
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private Security $security,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->queryBus->dispatch(new ListInvitationsQuery($user->getUserIdentifier()));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var list<InvitationView> $invitations */
        $invitations = $stamp->getResult();

        return new JsonResponse(array_map(static fn (InvitationView $inv) => $inv->toArray(), $invitations));
    }
}
