<?php

declare(strict_types=1);

namespace App\Shared\Distance;

use App\Shared\Type\ComparisonResult;

readonly class Distance
{
    public function __construct(
        private float $value,
        private Unit $unit,
    ) {
    }

    public function getValue(): float
    {
        return round($this->value, 5);
    }

    public function getUnit(): Unit
    {
        return $this->unit;
    }

    public function meters(): float
    {
        return match ($this->unit) {
            Unit::Meter => $this->value,
            Unit::Kilometer => $this->value * 1000,
        };
    }

    public function compareTo(Distance $distance): ComparisonResult
    {
        return match($this->meters() <=> $distance->meters()) {
            -1 => ComparisonResult::IsSmallerThan,
            0 => ComparisonResult::IsEqualTo,
            1 => ComparisonResult::IsBiggerThan,
        };
    }
}