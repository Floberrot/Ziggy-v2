<?php

declare(strict_types=1);

namespace App\Cat\Infrastructure\Input\Http;

use App\Cat\Application\Query\ListCats\CatView;
use App\Cat\Application\Query\ListCats\ListCatsQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Cats')]
#[Route('/api/cats', methods: ['GET'])]
#[OA\Get(
    path: '/api/cats',
    summary: 'List all cats belonging to the authenticated owner',
    responses: [
        new OA\Response(
            response: 200,
            description: 'List of cats',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'name', type: 'string', example: 'Mochi'),
                        new OA\Property(property: 'weight', type: 'number', nullable: true, example: 4.2),
                        new OA\Property(property: 'breed', type: 'string', nullable: true, example: 'Maine Coon'),
                        new OA\Property(property: 'colors', type: 'array', items: new OA\Items(type: 'string')),
                        new OA\Property(property: 'ownerId', type: 'string', format: 'uuid'),
                        new OA\Property(property: 'createdAt', type: 'string', format: 'date-time'),
                    ]
                )
            )
        ),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
final readonly class ListCatsController
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private Security $security,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->queryBus->dispatch(new ListCatsQuery(ownerId: $user->getUserIdentifier()));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var list<CatView> $cats */
        $cats = $stamp->getResult();

        return new JsonResponse(array_map(static fn (CatView $cat) => $cat->toArray(), $cats));
    }
}
