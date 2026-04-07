<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Input\Http;

use App\Admin\Application\Command\DeleteChipType\DeleteChipTypeAdminCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/chip-types/{chipTypeId}', methods: ['DELETE'])]
final readonly class DeleteChipTypeAdminController
{
    public function __construct(private MessageBusInterface $commandBus)
    {
    }

    public function __invoke(string $chipTypeId): JsonResponse
    {
        $this->commandBus->dispatch(new DeleteChipTypeAdminCommand($chipTypeId));

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
