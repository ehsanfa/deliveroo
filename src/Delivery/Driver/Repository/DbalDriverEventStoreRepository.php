<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Repository;

use App\Delivery\Driver;
use App\Delivery\Driver\Event\DriverAssigned;
use App\Delivery\Driver\Event\DriverCreated;
use App\Delivery\Driver\Event\DriverReserved;
use App\Shared\Type\DomainEvent;
use App\Shared\Type\DomainEventList;
use App\Shared\Type\EventStoreRepository;
use Doctrine\DBAL\Connection;

class DbalDriverEventStoreRepository implements EventStoreRepository
{
    public function __construct(
        private string $tableName,
        private Connection $connection,
        private Driver\DriverEventFactory $driverEventFactory,
    ) {
    }

    public function getEventsByIdentifier(string $identifier): DomainEventList
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 'es')
            ->select('es.*')
            ->where('es.event', ':identifier')
            ->setParameter('identifier', $identifier);

        $domainEventList = new DomainEventList([]);
        foreach ($qb->fetchAllAssociative() as $fetchedDomainEvent) {
            $domainEventList->append($this->getDomainEvent($fetchedDomainEvent));
        }

        return $domainEventList;
    }

     public function delete(DomainEventList $domainEventList): void
    {
        // TODO: Implement delete() method.
    }

    private function getDomainEvent(array $data): DomainEvent
    {
        return match ($data['identifier']) {
            DriverReserved::class => $this->driverEventFactory->createDriverReservedEvent($data['driver_id'], $data['payload']),
            DriverAssigned::class => $this->driverEventFactory->createDriverAssignedEvent($data['driver_id'], $data['payload']),
            DriverCreated::class => $this->driverEventFactory->createDriverCreatedEvent($data['driver_id']),
        };
    }
}