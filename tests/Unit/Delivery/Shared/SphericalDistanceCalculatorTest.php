<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Shared;

use App\Shared\Distance\SphericalDistanceCalculator;
use App\Shared\Distance\Unit;
use App\Shared\Type\Location;
use PHPUnit\Framework\TestCase;

class SphericalDistanceCalculatorTest extends TestCase
{
    public function testDistance(): void
    {
        $source = new Location(
            latitude: 49.870929,
            longitude: 8.646154,
        );
        $destination = new Location(
            latitude: 49.888188,
            longitude: 8.650662,
        );

        $distanceCalculator = new SphericalDistanceCalculator();
        $distance = $distanceCalculator->calculate(
            from: $source,
            to: $destination,
        );

        self::assertEquals(
            expected: 1.94601,
            actual: $distance->getValue(),
        );
        self::assertEquals(
            expected: Unit::Kilometer,
            actual: $distance->getUnit(),
        );
    }
}