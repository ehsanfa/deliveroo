<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Trip;
use App\Shared\Type\Command;

final readonly class UnreserveDriversCommand implements Command
{
    public function __construct(
        private Trip\Id $tripId,
    ) {
    }

    public function tripId(): Trip\Id
    {
        return $this->tripId;
    }
}