<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Command;

use App\Delivery\Trip\PersistingTripRepository;
use App\Delivery\Trip\Status;
use App\Delivery\Trip\Trip;

readonly class CreateTripHandler
{
    public function __construct(
        private PersistingTripRepository $persistingTripRepository,
    ) {
    }

    public function __invoke(CreateTripCommand $command): Trip
    {
        $trip = Trip::create(
            id: $command->getId(),
            status: Status::Open,
            source: $command->getSource(),
            destination: $command->getDestination(),
        );

        $this->persistingTripRepository->create($trip);

        return $trip;
    }
}