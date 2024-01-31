<?php

declare(strict_types=1);

namespace App\Delivery\DriverRate;

use App\Shared\Type\Id;

readonly class DriverRate
{
    public function __construct(
        private Id $driverId,
        private float $rate
    ) {
    }

    public function getDriverId(): Id
    {
        return $this->driverId;
    }

    public function getRate(): float
    {
        return $this->rate;
    }
}