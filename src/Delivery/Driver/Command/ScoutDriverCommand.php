<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\Scorer;
use App\Delivery\Trip\Trip;
use App\Shared\Type\Command;

readonly class ScoutDriverCommand implements Command
{
    public function __construct(
        private Trip   $trip,
        private Scorer $scorer,
    ) {
    }

    public function getTrip(): Trip
    {
        return $this->trip;
    }

    public function getScorer(): Scorer
    {
        return $this->scorer;
    }
}