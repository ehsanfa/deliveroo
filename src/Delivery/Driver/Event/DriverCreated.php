<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Event;

use App\Delivery\Driver;
use App\Shared\Type\ClassNameAsIdentifier;
use App\Shared\Type\DomainEvent;
use App\Shared\Type\Id;

readonly class DriverCreated implements DomainEvent
{
    use ClassNameAsIdentifier;

    public function __construct(
        private Driver\Id $driverId,
    ) {
    }

    public function getDriverId(): Driver\Id
    {
        return $this->driverId;
    }

    #[\Override]
    public function getAggregateRootId(): Id
    {
        return $this->driverId;
    }
}