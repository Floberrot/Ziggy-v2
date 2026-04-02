<?php

declare(strict_types=1);

namespace App\Identity\Infrastructure\Input\Http;

use App\Identity\Application\Query\ListPetSitters\ListPetSittersQuery;
use App\Identity\Application\Query\ListPetSitters\PetSitterView;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/pet-sitters', methods: ['GET'])]
final readonly class ListPetSittersController
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private Security $security,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->queryBus->dispatch(new ListPetSittersQuery(
            ownerEmail: $user->getUserIdentifier(),
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var list<PetSitterView> $views */
        $views = $stamp->getResult();

        return new JsonResponse(
            array_map(static fn (PetSitterView $v) => $v->toArray(), $views),
        );
    }
}
