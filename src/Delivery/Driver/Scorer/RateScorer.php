<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Scorer;

use App\Delivery\Driver\Scorer;
use App\Delivery\Driver\DriverList;
use App\Delivery\DriverRate\DriverRateRepository;
use App\Delivery\Shared\Configuration\ConfigurationManager;

readonly class RateScorer implements Scorer
{
    public function __construct(
        private DriverRateRepository $driverRateRepository,
        private ConfigurationManager $configurationManager,
    ) {
    }

    public function score(DriverList $driverList): DriverList
    {
        $driverRates = $this->driverRateRepository->getRateByDrivers($driverList);

        $drivers = [];
        foreach ($driverList as $driver) {
            $drivers[] = $driver;
            $driverRate = $driverRates->getRateByDriver($driver)?->getRate();
            if (null === $driverRate) {
                continue;
            }
            $factor = $this->getFactor(
                highestRate: $driverRates->getHighestRate(),
                driverRate: $driverRate,
            );
            $driver->multiplyScoreBy($factor);
        }

        return new DriverList($drivers);
    }

    private function getFactor(float $highestRate, float $driverRate): float
    {
        return ($driverRate*$this->configurationManager->scoutDriverRateScoreWeight())/$highestRate;
    }
}