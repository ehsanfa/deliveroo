<?php

declare(strict_types=1);

namespace App\Delivery\Driver;

interface Scorer
{
    public function score(DriverList $driverList): DriverList;
}