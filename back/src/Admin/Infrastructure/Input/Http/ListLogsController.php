<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Application\Query\ListLogs\ListLogsQuery;
use App\Shared\Application\DTO\PaginatedResult;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/logs', methods: ['GET'])]
final readonly class ListLogsController
{
    public function __construct(private MessageBusInterface $queryBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 50)));
        $userId = $request->query->getString('userId') ?: null;
        $logLevel = $request->query->getString('logLevel') ?: null;
        $search = $request->query->getString('search') ?: null;

        $envelope = $this->queryBus->dispatch(new ListLogsQuery(
            page: $page,
            limit: $limit,
            userId: $userId,
            logLevel: $logLevel,
            search: $search,
        ));

        /** @var HandledStamp $stamp */
        $stamp = $envelope->last(HandledStamp::class);

        /** @var PaginatedResult $result */
        $result = $stamp->getResult();

        return new JsonResponse($result->toArray());
    }
}
