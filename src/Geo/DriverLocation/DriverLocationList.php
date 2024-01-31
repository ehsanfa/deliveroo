<?php

declare(strict_types=1);

namespace App\Geo\DriverLocation;

use Traversable;

/**
 * @implements \IteratorAggregate<DriverLocation>
 */
readonly class DriverLocationList implements \IteratorAggregate
{
    /**
     * @param DriverLocation[] $driverLocations
     */
    public function __construct(
        private array $driverLocations,
    ) {
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->driverLocations);
    }
}