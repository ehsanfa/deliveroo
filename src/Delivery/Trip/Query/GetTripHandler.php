<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Query;

use App\Delivery\Trip\PersistingTripRepository;
use App\Delivery\Trip\Trip;

final readonly class GetTripHandler
{
    public function __construct(
        private PersistingTripRepository $tripRepository,
    ) {
    }

    public function __invoke(GetTripQuery $query): ?Trip
    {
        return $this->tripRepository->find($query->getTripId());
    }
}