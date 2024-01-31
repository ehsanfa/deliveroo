<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver;
use App\Delivery\Trip;
use App\Shared\Type\Command;

readonly class MarkDriverBusyCommand implements Command
{
    public function __construct(
        private Driver\Id $driverId,
        private Trip\Id $tripId,
    ) {
    }

    public function getDriverId(): Driver\Id
    {
        return $this->driverId;
    }

    public function getTripId(): Trip\Id
    {
        return $this->tripId;
    }
}