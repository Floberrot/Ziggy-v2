<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure;

use App\Shared\Domain\DomainEvent;
use App\Shared\Domain\EventBus;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerEventBus implements EventBus
{
    public function __construct(
        private MessageBusInterface $eventBus,
    ) {
    }

    public function publish(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->eventBus->dispatch($event);
        }
    }
}
