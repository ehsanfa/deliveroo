<?php

declare(strict_types=1);

namespace App\Shared\Type;

readonly class Location
{
    public function __construct(
        private float $latitude,
        private float $longitude,
    ) {
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function toArray(): array
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }

    public function isEqualTo(Location $location): bool
    {
        return $this->getLatitude() === $location->getLatitude()
            && $this->getLongitude() === $location->getLongitude();
    }
}