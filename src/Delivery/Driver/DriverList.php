<?php

declare(strict_types=1);

namespace App\Delivery\Driver;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\Scorer;
use Traversable;

/**
 * @implements \IteratorAggregate<Driver>
 */
readonly class DriverList implements \IteratorAggregate
{
    /**
     * @param Driver[] $drivers
     */
    public function __construct(private array $drivers = [])
    {
    }

    public function getDrivers(): array
    {
        return $this->drivers;
    }

    public function isEmpty(): bool
    {
        return count($this->drivers) === 0;
    }

    public function sortByScorer(Scorer $scorer): DriverList
    {
        $scoredDriverList = $scorer->score($this);
        return $scoredDriverList->sort(
            fn(Driver $a, Driver $b): int => $b->getScore() <=> $a->getScore(),
        );
    }

    private function sort(\Closure $closure): DriverList
    {
        $driverListToSort = $this->getDrivers();
        usort(
            $driverListToSort,
            $closure,
        );
        return new DriverList($driverListToSort);
    }

    public function first(): ?Driver
    {
        return $this->drivers[0] ?? null;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->drivers);
    }
}