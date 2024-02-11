<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Repository;

use App\Delivery\Driver\Driver;
use App\Delivery\Trip\Id;
use App\Delivery\Trip\PersistingTripRepository;
use App\Delivery\Trip\ReadOnlyTripRepository;
use App\Delivery\Trip\Trip;
use App\Delivery\Trip\TripList;
use App\Shared\Type\DomainEventWithPayload;
use App\Shared\Type\UuidGenerator;
use App\Shared\Type\UuidValidator;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class DbalTripRepository implements PersistingTripRepository, ReadOnlyTripRepository
{
    public function __construct(
        private string $tableName,
        private string $eventStoreTableName,
        private Connection $connection,
        private UuidValidator $uuidValidator,
        private UuidGenerator $uuidGenerator,
    ) {
    }

    public function find(Id $id): ?Trip
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 't')
            ->select(
                't.id',
                't.status',
                't.driver_id',
                'ST_Latitude(source) as source_latitude',
                'ST_Longitude(source) as source_longitude',
                'ST_Latitude(destination) as destination_latitude',
                'ST_Longitude(destination) as destination_longitude',
            )
            ->where('t.id', ':id');
        $qb->setParameter('id', $id->toString());
        $res = $qb->fetchAssociative();

        if (false === $res) {
            return null;
        }

        return Trip::fromArray($res, $this->uuidValidator);
    }

    public function create(Trip $trip): void
    {
        if (!$trip->isFresh()) {
            return;
        }

        $q = $this->connection->prepare("
            INSERT INTO {$this->tableName}
            (`id`, `status`, `source`, `destination`)
            VALUES (
                :id,
                :status,
                ST_SRID(POINT(:source_lng, :source_lat), 4326),
                ST_SRID(POINT(:destination_lng, :destination_lat), 4326)
            )
        ");
        $q->bindValue("id", $trip->getId()->toString());
        $q->bindValue("status", $trip->getStatus()->value);
        $q->bindValue("source_lat", $trip->getSource()->getLatitude());
        $q->bindValue("source_lng", $trip->getSource()->getLongitude());
        $q->bindValue("destination_lat", $trip->getDestination()->getLatitude());
        $q->bindValue("destination_lng", $trip->getDestination()->getLongitude());
        $q->executeQuery();

    }

    /**
     * @throws Exception
     */
    public function update(Trip $trip): void
    {
        if (!$trip->isDirty()) {
            return;
        }

        $changes = [];
        foreach ($trip->getChangesets() as $changeset) {
            if ($changeset->getField() === 'status') {
                $changes['status'] = $changeset->getNew();
            }
            if ($changeset->getField() === 'driver_id') {
                $changes['driver_id'] = $changeset->getNew();
            }
        }

        $this->connection->beginTransaction();
        try {
            $this->connection->update(
                table: $this->tableName,
                data: $changes,
                criteria: [
                    'id' => $trip->getId()->toString(),
                ],
            );

            foreach ($trip->getDomainEvents() as $domainEvent) {
                $payload = null;
                if ($domainEvent instanceof DomainEventWithPayload) {
                    $payload = $domainEvent->getPayload();
                }
                $this->connection->insert($this->eventStoreTableName, [
                    'id' => $this->uuidGenerator->generate()->toString(),
                    'trip_id' => $domainEvent->getAggregateRootId()->toString(),
                    'event' => $domainEvent::getIdentifier(),
                    'payload' => $payload !== null ? json_encode($payload) : null,
                ]);
            }
            $trip->flushDomainEvents();

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    #[\Override] public function delete(Trip $trip): void
    {
        // TODO: Implement delete() method.
    }

    #[\Override] public function getOpenTrips(): TripList
    {
        // TODO: Implement getOpenTrips() method.
    }

    #[\Override] public function driverHasDoneMoreTripsThan(Driver $driver, int $trips): bool
    {
        return false;
    }
}