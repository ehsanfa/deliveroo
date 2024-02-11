<?php

declare(strict_types=1);

namespace App\Delivery\DriverRate;

use App\Delivery\Driver\DriverList;

interface DriverRateRepository
{
    public function getRateByDrivers(DriverList $driver): DriverRateList;
}