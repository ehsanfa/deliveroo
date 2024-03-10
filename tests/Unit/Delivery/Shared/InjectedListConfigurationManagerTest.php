<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Shared;

use App\Delivery\Shared\Configuration\InjectedListConfigurationManager;
use App\Shared\Distance\Distance;
use App\Shared\Distance\Unit;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class InjectedListConfigurationManagerTest extends KernelTestCase
{
    public function testGetConfigScout(): void
    {
        $configManager = new InjectedListConfigurationManager(
            configs: [
                'scout' => [
                    'maxDistanceValue' => 5,
                    'maxDistanceUnit' => Unit::Kilometer,
                ]
            ]
        );
        self::assertEquals(
            expected: new Distance(5, Unit::Kilometer),
            actual: $configManager->scoutDriverMaxDistanceBikersAround(),
        );
        self::assertEquals(
            expected: new Distance(5, Unit::Kilometer),
            actual: $configManager->scoutDriverMaxDistanceBikersAround(),
        );
    }
}