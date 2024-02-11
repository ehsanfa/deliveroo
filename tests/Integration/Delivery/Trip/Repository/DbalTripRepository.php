<?php

declare(strict_types=1);

namespace Integration\Delivery\Trip\Repository;

use App\Delivery\Trip\Id;
use App\Delivery\Trip\PersistingTripRepository;
use App\Delivery\Trip\Status;
use App\Delivery\Trip\Trip;
use App\Shared\Type\Location;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidGenerator;
use App\Shared\Type\UuidValidator;
use Doctrine\DBAL\Connection;
use Test\Integration\Shared\TestWithCleanup;

class DbalTripRepository extends TestWithCleanup
{
    public function testCreateTrip(): void
    {
        $uuidValidator = $this->getContainer()->get(UuidValidator::class);
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $tripId = new Id($uuidGenerator->generate());
        /** @var PersistingTripRepository $repo */
        $repo = $this->getContainer()->get(PersistingTripRepository::class);
        $trip = Trip::create(
            id: $tripId,
            status: Status::Open,
            source: new Location(latitude: 34.5423, longitude: 53.3432),
            destination: new Location(latitude: 34.65334, longitude: 53.42533),
        );
        $repo->create($trip);
        $trip = $repo->find($tripId);
        self::assertNotNull($trip);
    }

    public function testDoesNotFindTrip(): void
    {
        $uuidValidator = $this->getContainer()->get(UuidValidator::class);
        /** @var PersistingTripRepository $repo */
        $repo = $this->getContainer()->get(PersistingTripRepository::class);
        $trip = $repo->find(new Id(Uuid::fromString(
            string: "568ec104-ffff-4980-a431-1c3d0cfd997f",
            validator: $uuidValidator,
        )));
        self::assertNull($trip);
    }
}