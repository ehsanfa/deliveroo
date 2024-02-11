<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Port\Persistence\Repository;

use App\Delivery\Driver;
use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverCreated;
use App\Delivery\Driver\Event\DriverReserved;
use App\Delivery\Shared\DomainEventEntity;
use App\Delivery\Shared\DomainEventEntityList;
use App\Delivery\Shared\DomainEventId;
use App\Delivery\Shared\EventStoreRepository;
use App\Delivery\Shared\Exception\DomainEventNotFoundException;
use App\Delivery\Shared\Exception\UndefinedDomainEventException;
use App\Shared\Type\DomainEvent;
use App\Shared\Type\InvalidUuidException;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

final readonly class DbalDriverEventStoreRepository implements EventStoreRepository
{
    public function __construct(
        private string $tableName,
        private Connection $connection,
        private Driver\DriverEventFactory $driverEventFactory,
        private UuidValidator $uuidValidator,
    ) {
    }

    public function getEventsByIdentifier(string $identifier): DomainEventEntityList
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 'es')
            ->select('es.*')
            ->where('es.event = :identifier')
            ->setParameter('identifier', $identifier)
            ;

        $domainEventList = new DomainEventEntityList([]);
        foreach ($qb->fetchAllAssociative() as $fetchedDomainEvent) {
            $domainEventList = $domainEventList->append(
                new DomainEventEntity(
                    new DomainEventId(Uuid::fromString(
                        string: $fetchedDomainEvent['id'],
                        validator: $this->uuidValidator,
                    )),
                    $this->getDomainEvent($fetchedDomainEvent),
                ),
            );
        }

        return $domainEventList;
    }

    /**
     * @inheritDoc
     */
    public function delete(array $ids): void
    {
        $idsToDelete = [];
        foreach ($ids as $id) {
            $idsToDelete[] = $id->toString();
        }

        $qb = $this->connection->createQueryBuilder();
        $qb->delete($this->tableName)
            ->andWhere($qb->expr()->in('id', ':ids'))
            ->setParameter('ids', $idsToDelete, ArrayParameterType::STRING)
            ->executeQuery();
    }

    /**
     * @throws UndefinedDomainEventException
     */
    private function getDomainEvent(array $data): DomainEvent
    {
        $payload = isset($data['payload']) ? json_decode($data['payload'], true) : null;
        return match ($data['event']) {
            DriverReserved::class => $this->driverEventFactory->createDriverReservedEvent($data['driver_id'], $payload),
            DriverAssigned::class => $this->driverEventFactory->createDriverAssignedEvent($data['driver_id'], $payload),
            DriverCreated::class => $this->driverEventFactory->createDriverCreatedEvent($data['driver_id']),
            default => throw new UndefinedDomainEventException(),
        };
    }

    /**
     * @inheritDoc
     */
    public function getEventById(DomainEventId $id): DomainEvent
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 'es')
            ->select('es.*')
            ->where('es.id = :id')
            ->setParameter('id', $id->toString());

        $domainEventArray = $qb->fetchAssociative();

        if (false === $domainEventArray) {
            throw new DomainEventNotFoundException();
        }

        return $this->getDomainEvent($domainEventArray);
    }

    /**
     * @throws InvalidUuidException
     * @throws Exception
     * @inheritDoc
     */
    public function getOldestEventIds(int $limit): \Traversable
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 'es')
            ->select('es.id')
            ->orderBy('es.id', 'desc')
            ->setMaxResults($limit);

        $domainEventIds = $qb->fetchAssociative();

        foreach ($qb->fetchFirstColumn() as $domainEventId) {
            yield new DomainEventId(Uuid::fromString($domainEventId, $this->uuidValidator));
        }
    }
}