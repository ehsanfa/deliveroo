<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Scorer;

use App\Delivery\Driver\Scorer;
use App\Delivery\DriverRate\DriverList;
use App\Delivery\Shared\Configuration\ConfigurationManager;
use App\Delivery\Trip\ReadOnlyTripRepository;

readonly class RookieScorer implements Scorer
{
    public function __construct(
        private ReadOnlyTripRepository $tripRepository,
        private ConfigurationManager $configurationManager,
    ) {
    }

    #[\Override]
    public function score(DriverList $driverList): DriverList
    {
        foreach ($driverList as $driver) {
            if ($driver->isRookie($this->tripRepository)) {
                $driver->multiplyScoreBy($this->configurationManager->scoutDriverRookieScoreWeight());
            }
        }

        return $driverList;
    }
}