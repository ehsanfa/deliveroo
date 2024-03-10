<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Port\Persistence\Repository;

use App\Delivery\Shared\DomainEventEntity;
use App\Delivery\Shared\DomainEventEntityList;
use App\Delivery\Shared\DomainEventFactory;
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

final readonly class DbalEventStoreRepository implements EventStoreRepository
{
    public function __construct(
        private string $tableName,
        private Connection $connection,
        private DomainEventFactory $domainEventFactory,
        private UuidValidator $uuidValidator,
    ) {
    }

    /**
     * @throws UndefinedDomainEventException
     * @throws InvalidUuidException
     * @throws Exception
     */
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
                    $this->domainEventFactory->getDomainEvent($fetchedDomainEvent),
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
     * @inheritDoc
     * @throws UndefinedDomainEventException|Exception
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

        return $this->domainEventFactory->getDomainEvent($domainEventArray);
    }

    /**
     * @throws Exception|UndefinedDomainEventException
     *
     */
    public function getOldestEvents(int $limit): DomainEventEntityList
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->from($this->tableName, 'es')
            ->select('es.*')
            ->orderBy('es.id', 'asc')
            ->setMaxResults($limit);

        $domainEventEntities = [];
        foreach ($qb->fetchAllAssociative() as $domainEventEntityArray) {
            $domainEventEntities[] = new DomainEventEntity(
                new DomainEventId(Uuid::fromString($domainEventEntityArray['id'], $this->uuidValidator)),
                $this->domainEventFactory->getDomainEvent($domainEventEntityArray),
            );
        }

        return new DomainEventEntityList($domainEventEntities);
    }
}