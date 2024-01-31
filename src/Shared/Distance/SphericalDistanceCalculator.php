<?php

declare(strict_types=1);

namespace App\Shared\Distance;

use App\Shared\Type\Location;

readonly class SphericalDistanceCalculator implements DistanceCalculator
{
    public function calculate(Location $from, Location $to): Distance
    {
        if ($from->isEqualTo($to)) {
            return new Distance(0, Unit::Meter);
        }
        else {
            $theta = $from->getLongitude() - $to->getLongitude();
            $distance = (sin(deg2rad($from->getLatitude())) * sin(deg2rad($to->getLatitude())))
                + (
                    cos(deg2rad($from->getLatitude()))
                    * cos(deg2rad($to->getLatitude()))
                    * cos(deg2rad($theta))
                )
            ;
            $distance = acos($distance);
            $distance = rad2deg($distance);
            $miles = $distance * 60 * 1.1515;

            $distanceInKilometers = $miles * 1.609344;

            return new Distance(
                value: $distanceInKilometers,
                unit: Unit::Kilometer,
            );
        }
    }
}