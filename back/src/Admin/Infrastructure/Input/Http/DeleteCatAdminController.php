<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Application\Command\DeleteCat\DeleteCatAdminCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/cats/{catId}', methods: ['DELETE'])]
final readonly class DeleteCatAdminController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $catId): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteCatAdminCommand($catId));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
