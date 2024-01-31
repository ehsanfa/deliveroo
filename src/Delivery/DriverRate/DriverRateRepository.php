<?php

declare(strict_types=1);

namespace App\Delivery\DriverRate;

interface DriverRateRepository
{
    public function getRateByDrivers(DriverList $driver): DriverRateList;
}