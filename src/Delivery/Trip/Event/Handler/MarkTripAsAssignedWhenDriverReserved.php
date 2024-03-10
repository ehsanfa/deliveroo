<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Event\Handler;

use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Trip\Command\RecruitDriverCommand;
use App\Delivery\Trip\Exception\TripIsNotOpenException;
use App\Shared\Type\CommandBus;

final readonly class MarkTripAsAssignedWhenDriverReserved
{
    public function __construct(
        private CommandBus $tripCommandBus,
    ) {
    }

    /**
     * @throws TripIsNotOpenException
     */
    public function handle(DriverReserved $domainEvent): void
    {
        $this->tripCommandBus->handle(new RecruitDriverCommand($domainEvent->getTripId()));
    }
}