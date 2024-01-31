<?php

declare(strict_types=1);

namespace App\Delivery\Driver;

use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverCreated;
use App\Delivery\Driver\Event\DriverReserved;

interface DriverEventFactory
{
    public function createDriverAssignedEvent(mixed $driverId, mixed $tripId): DriverAssigned;

    public function createDriverCreatedEvent(mixed $driverId): DriverCreated;

    public function createDriverReservedEvent(mixed $driverId, mixed $tripId): DriverReserved;
}