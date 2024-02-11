<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver;
use App\Shared\Type\Command;

final readonly class MarkDriverFreeCommand implements Command
{
    public function __construct(
        private Driver\Id $driverId,
    ) {
    }

    public function getDriverId(): Driver\Id
    {
        return $this->driverId;
    }
}