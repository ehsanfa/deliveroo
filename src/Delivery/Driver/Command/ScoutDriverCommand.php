<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\Scorer;
use App\Delivery\Trip;
use App\Shared\Type\Command;

final readonly class ScoutDriverCommand implements Command
{
    public function __construct(
        private Trip\Id   $tripId,
        private Scorer $scorer,
    ) {
    }

    public function getTripId(): Trip\Id
    {
        return $this->tripId;
    }

    public function getScorer(): Scorer
    {
        return $this->scorer;
    }
}