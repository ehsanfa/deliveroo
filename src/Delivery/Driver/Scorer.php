<?php

declare(strict_types=1);

namespace App\Delivery\Driver;

use App\Delivery\DriverRate\DriverList;

interface Scorer
{
    public function score(DriverList $driverList): DriverList;
}