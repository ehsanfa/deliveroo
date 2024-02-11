<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Query;

use App\Delivery\Driver;
use App\Shared\Type\Query;

final readonly class GetDriverQuery implements Query
{
    public function __construct(
        private Driver\Id $driverId,
    ) {
    }

    public function getDriverId(): Driver\Id
    {
        return $this->driverId;
    }
}