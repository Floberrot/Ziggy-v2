<?php

declare(strict_types=1);

namespace App\ChipType\Infrastructure\Input\Http;

use App\ChipType\Application\Query\ListChipTypes\ChipTypeView;
use App\ChipType\Application\Query\ListChipTypes\ListChipTypesQuery;
use OpenApi\Attributes as OA;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[OA\Tag(name: 'Chip Types')]
#[Route('/api/chip-types', methods: ['GET'])]
#[OA\Get(
    path: '/api/chip-types',
    summary: 'List chip types for the authenticated owner',
    responses: [
        new OA\Response(response: 200, description: 'List of chip types'),
        new OA\Response(response: 401, description: 'Unauthorized'),
    ]
)]
final readonly class ListChipTypesController
{
    public function __construct(
        private MessageBusInterface $queryBus,
        private Security $security,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $user = $this->security->getUser() ?? throw new \LogicException('User not authenticated.');

        $envelope = $this->queryBus->dispatch(new ListChipTypesQuery($user->getUserIdentifier()));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var list<ChipTypeView> $chipTypes */
        $chipTypes = $stamp->getResult();

        return new JsonResponse(array_map(static fn (ChipTypeView $ct) => $ct->toArray(), $chipTypes));
    }
}
