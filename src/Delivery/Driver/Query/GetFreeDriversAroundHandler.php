<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Query;

use App\Delivery\Driver\DriverList;
use App\Delivery\Driver\DriverRepository;
use App\Shared\Type\Query;

final readonly class GetFreeDriversAroundHandler implements Query
{
    public function __construct(
        private DriverRepository $driverRepository,
    ) {
    }

    public function __invoke(GetFreeDriversAroundQuery $query): DriverList
    {
        return $this->driverRepository->getFreeDriversAround(
            location: $query->getLocation(),
            distance: $query->getMaxDistance(),
            lastActivityUntil: $query->getLastActivityUntil(),
        );
    }
}