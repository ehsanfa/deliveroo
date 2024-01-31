<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Command;

use App\Delivery\Trip\Id;
use App\Shared\Type\Command;
use App\Shared\Type\Location;

readonly class CreateTripCommand implements Command
{
    public function __construct(
        private Id $id,
        private Location $source,
        private Location $destination,
    ) {
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getSource(): Location
    {
        return $this->source;
    }

    public function getDestination(): Location
    {
        return $this->destination;
    }
}