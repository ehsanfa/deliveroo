<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event\Handler;

use App\Delivery\Driver\Command\MarkDriverBusyCommand;
use App\Delivery\Trip\Event\TripMarkedAsInProgress;
use App\Shared\Type\CommandBus;

final readonly class MarkDriverAsBusyWhenTripIsMarkedAsAssigned
{
    public function __construct(
        private CommandBus $driverCommandBus,
    ) {
    }

    public function handle(TripMarkedAsInProgress $domainEvent): void
    {
        $this->driverCommandBus->handle(new MarkDriverBusyCommand(
            driverId: $domainEvent->driverId(),
            tripId: $domainEvent->tripId(),
        ));
    }
}