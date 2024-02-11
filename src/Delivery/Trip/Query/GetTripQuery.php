<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Query;

use App\Delivery\Trip\Id;
use App\Shared\Type\Query;

final readonly class GetTripQuery implements Query
{
    public function __construct(
        private Id $tripId
    ) {
    }

    public function getTripId(): Id
    {
        return $this->tripId;
    }
}