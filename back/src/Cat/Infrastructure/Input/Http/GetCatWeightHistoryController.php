<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Input\Http;

use App\Cat\Application\Query\GetCatWeightHistory\GetCatWeightHistoryQuery;
use App\Cat\Application\Query\GetCatWeightHistory\WeightEntryView;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Cats')]
#[Route('/api/cats/{id}/weight-history', methods: ['GET'])]
#[OA\Get(
    path: '/api/cats/{id}/weight-history',
    summary: 'Get weight history for a cat',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    responses: [
        new OA\Response(response: 200, description: 'Weight history entries'),
        new OA\Response(response: 404, description: 'Cat not found'),
    ]
)]
final readonly class GetCatWeightHistoryController
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private Security $security,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->queryBus->dispatch(new GetCatWeightHistoryQuery(
            catId: $id,
            requestingUserId: $user->getUserIdentifier(),
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var list<WeightEntryView> $entries */
        $entries = $stamp->getResult();

        return new JsonResponse(array_map(
            static fn (WeightEntryView $entry) => $entry->toArray(),
            $entries,
        ));
    }
}
