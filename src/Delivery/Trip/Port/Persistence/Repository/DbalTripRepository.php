<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Port\Persistence\Repository;

use App\Delivery\Driver;
use App\Delivery\Trip\Id;
use App\Delivery\Trip\PersistingTripRepository;
use App\Delivery\Trip\ReadOnlyTripRepository;
use App\Delivery\Trip\Status;
use App\Delivery\Trip\Trip;
use App\Delivery\Trip\TripList;
use App\Shared\Type\DomainEventWithPayload;
use App\Shared\Type\Location;
use App\Shared\Type\Uuid;
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
            ->where('t.id = :id');
        $qb->setParameter('id', $id->toString());
        $res = $qb->fetchAssociative();

        if (false === $res) {
            return null;
        }

        $id = new Id(Uuid::fromString($res['id'], $this->uuidValidator));
        $status = Status::from($res['status']);
        $source = new Location(
            latitude: $res['source_latitude'],
            longitude: $res['source_longitude'],
        );
        $destination = new Location(
            latitude: $res['destination_latitude'],
            longitude: $res['destination_longitude'],
        );

        $driverId = null;
        if (null !== $res['driver_id']) {
            $driverId = new Driver\Id(Uuid::fromString($res['driver_id'], $this->uuidValidator));
        }

        return Trip::fromData(
            $id,
            $status,
            $source,
            $destination,
            $driverId,
        );
    }

    /**
     * @throws Exception
     */
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

        $this->connection->beginTransaction();
        try {
            $q->executeQuery();
            $this->persistDomainEvents($trip);
            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }

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

            $this->persistDomainEvents($trip);

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    private function persistDomainEvents(Trip $trip): void
    {
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
    }

    #[\Override] public function delete(Trip $trip): void
    {
        // TODO: Implement delete() method.
    }

    #[\Override] public function getOpenTrips(): TripList
    {
        // TODO: Implement getOpenTrips() method.
    }

    #[\Override] public function driverHasDoneMoreTripsThan(Driver\Id $driverId, int $trips): bool
    {
        return false;
    }

    public function nextIdentity(): Id
    {
        return new Id($this->uuidGenerator->generate());
    }
}