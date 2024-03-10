<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Command;

use App\Delivery\Shared\Bus\QueryBus;
use App\Delivery\Shared\Exception\TripNotFoundException;
use App\Delivery\Trip\PersistingTripRepository;
use App\Delivery\Trip\Query\GetTripQuery;
use App\Shared\Type\HandlerNotFoundException;

final readonly class MarkTripAsDeliveredHandler
{
    public function __construct(
        private QueryBus $tripQueryBus,
        private PersistingTripRepository $tripRepository,
    ) {
    }

    /**
     * @throws TripNotFoundException
     * @throws HandlerNotFoundException
     */
    public function __invoke(MarkTripAsDeliveredCommand $command): void
    {
        $trip = $this->tripQueryBus->handle(new GetTripQuery($command->tripId()));
        if (null === $trip) {
            throw new TripNotFoundException();
        }

        $trip->markAsDelivered();
        $this->tripRepository->update($trip);
    }
}