<?php

declare(strict_types=1);

namespace App\Delivery\Shared\EventHandler;

use App\Shared\Type\DomainEvent;
use App\Shared\Type\DomainEventHandler;
use App\Shared\Type\EventDispatcher;

readonly class EventDispatcherImplementation implements EventDispatcher
{
    /**
     * @param array<string, DomainEventHandler[]> $handlers
     */
    public function __construct(private array $handlers = [])
    {
    }

    #[\Override]
    public function dispatch(DomainEvent $domainEvent): void
    {
        foreach ($this->handlers as $eventIdentifier => $handlers) {
            if ($domainEvent::getIdentifier() !== $eventIdentifier) {
                continue;
            }
            foreach ($handlers as $handler) {
                $handler->handle($domainEvent);
            }
        }
    }
}