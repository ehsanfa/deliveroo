<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event\Handler;

use App\Delivery\Driver\Command\ScoutDriverCommand;
use App\Delivery\Driver\Scorer;
use App\Delivery\Shared\Bus\CommandBus;
use App\Delivery\Trip\Event\TripCreated;

final readonly class ScoutDriversWhenTripCreated
{
    public function __construct(
        private CommandBus $driverCommandBus,
        private Scorer $scorer,
    ) {
    }

    public function handle(TripCreated $event): void
    {
        $this->driverCommandBus->handle(new ScoutDriverCommand(
            $event->getTripId(),
            $this->scorer,
        ));
    }
}