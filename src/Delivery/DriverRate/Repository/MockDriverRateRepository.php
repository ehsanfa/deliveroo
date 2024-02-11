<?php

declare(strict_types=1);

namespace App\Delivery\DriverRate\Repository;

use App\Delivery\Driver\DriverList;
use App\Delivery\DriverRate\DriverRateList;
use App\Delivery\DriverRate\DriverRateRepository;

final readonly class MockDriverRateRepository implements DriverRateRepository
{
    public function getRateByDrivers(DriverList $driver): DriverRateList
    {
        return new DriverRateList([]);
    }
}