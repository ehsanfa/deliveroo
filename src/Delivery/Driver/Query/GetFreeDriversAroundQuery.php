<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Query;

use App\Shared\Distance\Distance;
use App\Shared\Type\Location;
use App\Shared\Type\Query;

final readonly class GetFreeDriversAroundQuery implements Query
{
    public function __construct(
        private Location $location,
        private Distance $maxDistance,
        private \DateTimeImmutable $lastActivityUntil,
    ) {
    }

    public function getMaxDistance(): Distance
    {
        return $this->maxDistance;
    }

    public function getLastActivityUntil(): \DateTimeImmutable
    {
        return $this->lastActivityUntil;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }
}