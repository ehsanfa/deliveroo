<?php

declare(strict_types=1);

namespace App\Delivery\Trip;

readonly class TripList
{
    /**
     * @param Trip[] $trips
     */
    public function __construct(private array $trips = [])
    {
    }

    public function getTrips(): array
    {
        return $this->trips;
    }
}