<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event\Handler;

use App\Delivery\Driver\Command\MarkDriverFreeCommand;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Shared\Bus\QueryBus;
use App\Delivery\Trip\Event\TripDelivered;
use App\Delivery\Trip\Query\GetTripQuery;
use App\Shared\Type\CommandBus;
use App\Shared\Type\HandlerNotFoundException;

final readonly class MarkDriverAsFreeWhenTripDelivered
{
    public function __construct(
        protected QueryBus $tripQueryBus,
        private CommandBus $driverCommandBus,
    ) {
    }

    /**
     * @throws HandlerNotFoundException
     * @throws DriverNotFoundException
     */
    public function handle(TripDelivered $domainEvent): void
    {
        $trip = $this->tripQueryBus->handle(
            new GetTripQuery($domainEvent->getTripId()),
        );
        if (null === $trip) {
            return;
        }
        $this->driverCommandBus->handle(new MarkDriverFreeCommand(
            $trip->getDriverId(),
        ));
    }
}