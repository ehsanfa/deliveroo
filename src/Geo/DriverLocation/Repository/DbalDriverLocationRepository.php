<?php

declare(strict_types=1);

namespace App\Geo\DriverLocation\Repository;

use App\Geo\DriverLocation\DriverLocation;
use App\Geo\DriverLocation\PersistingDriverLocationRepository;
use App\Geo\DriverLocation\ReadOnlyDriverLocationRepository;
use Doctrine\DBAL\Driver\Connection;

final readonly class DbalDriverLocationRepository implements PersistingDriverLocationRepository, ReadOnlyDriverLocationRepository
{
    public function __construct(
        private string $tableName,
        private Connection $connection,
    ) {

    }

    public function saveLocation(DriverLocation $driverLocation): void
    {
        if (!$driverLocation->getIsFresh()) {
            return;
        }

        $this->connection->insert(
            table: $this->tableName,
            data: [
                'id' => $driver->getId()->toString(),
                'status' => $driver->getStatus()->value,
            ],
        );
    }
}