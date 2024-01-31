<?php

declare(strict_types=1);

namespace App\Shared\Distance;

use App\Shared\Type\Location;

interface DistanceCalculator
{
    public function calculate(Location $from, Location $to): Distance;
}