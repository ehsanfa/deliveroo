<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Query;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;

final readonly class GetDriverHandler
{
    public function __construct(
        private DriverRepository $driverRepository
    ) {
    }

    public function __invoke(GetDriverQuery $query): ?Driver
    {
        return $this->driverRepository->find($query->getDriverId());
    }
}