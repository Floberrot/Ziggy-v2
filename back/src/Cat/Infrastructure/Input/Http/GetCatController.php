<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Input\Http;

use App\Cat\Application\Query\GetCat\GetCatQuery;
use App\Cat\Application\Query\ListCats\CatView;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Cats')]
#[Route('/api/cats/{id}', methods: ['GET'])]
#[OA\Get(
    path: '/api/cats/{id}',
    summary: 'Get a single cat',
    parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string'))],
    responses: [
        new OA\Response(response: 200, description: 'Cat details'),
        new OA\Response(response: 404, description: 'Cat not found'),
    ]
)]
final readonly class GetCatController
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private Security $security,
    ) {
    }

    public function __invoke(string $id): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->queryBus->dispatch(new GetCatQuery(
            catId: $id,
            requestingUserId: $user->getUserIdentifier(),
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var CatView $view */
        $view = $stamp->getResult();

        return new JsonResponse($view->toArray());
    }
}
