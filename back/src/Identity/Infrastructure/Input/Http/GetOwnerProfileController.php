<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Query\GetOwnerProfile\GetOwnerProfileQuery;
use App\Identity\Application\Query\GetOwnerProfile\OwnerProfileView;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/profile', methods: ['GET'])]
final readonly class GetOwnerProfileController
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private Security $security,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->queryBus->dispatch(new GetOwnerProfileQuery(
            ownerEmail: $user->getUserIdentifier(),
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var OwnerProfileView $view */
        $view = $stamp->getResult();

        return new JsonResponse($view->toArray());
    }
}
