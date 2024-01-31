<?php

declare(strict_types=1);

namespace App\Delivery\DriverRate;

use App\Delivery\Driver\Driver;

readonly class DriverRateList
{
    /**
     * @var DriverRate[]
     */
    private array $driverRates;
    private float $highestRate;

    /**
     * @param DriverRate[] $driverRates
     */
    public function __construct(
        array $driverRates
    ) {
        $highestRate = 0;
        $deliverRatesList = [];
        foreach ($driverRates as $driverRate) {
            $driverId = $driverRate->getDriverId()->toString();
            $deliverRatesList[$driverId] = $driverRate;
            if ($driverRate->getRate() > $highestRate) {
                $highestRate = $driverRate->getRate();
            }
        }
        $this->driverRates = $deliverRatesList;
        $this->highestRate = $highestRate;
    }

    public function getHighestRate(): float
    {
        return $this->highestRate;
    }

    /**
     * @return DriverRate[]
     */
    public function getDriverRates(): array
    {
        return array_values($this->driverRates);
    }

    public function getRateByDriver(Driver $driver): ?DriverRate
    {
        return $this->driverRates[$driver->getId()->toString()] ?? null;
    }
}