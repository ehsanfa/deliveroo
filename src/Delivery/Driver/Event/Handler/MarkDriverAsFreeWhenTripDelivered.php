<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event\Handler;

use App\Delivery\Driver\Command\MarkDriverFreeCommand;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Trip\Event\TripDelivered;
use App\Delivery\Trip\PersistingTripRepository;
use App\Shared\Type\CommandBus;
use App\Shared\Type\HandlerNotFoundException;

final readonly class MarkDriverAsFreeWhenTripDelivered
{
    public function __construct(
        private PersistingTripRepository $tripRepository,
        private CommandBus $driverCommandBus,
    ) {
    }

    /**
     * @throws HandlerNotFoundException
     * @throws DriverNotFoundException
     */
    public function handle(TripDelivered $domainEvent): void
    {
        $trip = $this->tripRepository->find($domainEvent->getTripId());
        if (null === $trip) {
            return;
        }
        $this->driverCommandBus->handle(new MarkDriverFreeCommand(
            $trip->getDriverId(),
        ));
    }
}