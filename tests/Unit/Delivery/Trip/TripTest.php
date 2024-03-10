<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Trip;

use App\Delivery\Driver;
use App\Delivery\Shared\Exception\HydrationException;
use App\Delivery\Trip\Id as TripId;
use App\Delivery\Trip\Status;
use App\Delivery\Trip\Status as TripStatus;
use App\Delivery\Trip\Trip;
use App\Shared\Type\Location;
use PHPUnit\Framework\TestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class TripTest extends TestCase
{
    public function testMarkAsDelivered(): void
    {
        $trip = Trip::create(
            id: new TripId (
                id: (new MockUuid())->generate(),
            ),
            status: TripStatus::Open,
            source: new Location(
                latitude: 25.2422,
                longitude: 52.2533,
            ),
            destination: new Location(
                latitude: 25.2412,
                longitude: 52.2523,
            ),
        );
        $trip->markAsDelivered();
        $changesets = $trip->getChangesets();

        self::assertCount(
            expectedCount: 1,
            haystack: $changesets,
        );
        foreach ($changesets as $changeset) {
            self::assertEquals(
                expected: 'status',
                actual: $changeset->getField(),
            );
            self::assertEquals(
                expected: TripStatus::Open,
                actual: TripStatus::tryFrom($changeset->getOld()),
            );
            /** @var Trip $new */
            self::assertEquals(
                expected: TripStatus::Finished,
                actual: TripStatus::tryFrom($changeset->getNew()),
            );
        }
    }

    public function testFromDataSucceeds(): void
    {
        $driverId = new Driver\Id((new MockUuid())->generate());
        $trip = Trip::fromData(
            id: new TripId((new MockUuid())->generate()),
            status: Status::Open,
            source: new Location(34.5234, 53.43245),
            destination: new Location(34.4234, 53.53245),
            driverId: $driverId,
        );
        self::assertEquals(
            expected: 1,
            actual: $trip->getStatus()->value,
        );
        self::assertEquals(
            expected: $driverId->toString(),
            actual: $trip->getDriverId()->toString(),
        );
    }
}