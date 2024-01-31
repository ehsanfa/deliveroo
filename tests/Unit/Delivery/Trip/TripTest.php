<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Trip;

use App\Delivery\Shared\Exception\HydrationException;
use App\Delivery\Trip\Id as TripId;
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
                expected: TripStatus::Open->name,
                actual: $changeset->getOld(),
            );
            /** @var Trip $new */
            self::assertEquals(
                expected: TripStatus::Finished->name,
                actual: $changeset->getNew(),
            );
        }
    }

    public function testFromArraySucceeds(): void
    {
        $data = [
            "id" => "568ec104-ffff-4980-a431-1c3d0cfd997f",
            "status" => 1,
            "source" => [
                "latitude" => 34.5234,
                "longitude" => 53.43245,
            ],
            "destination" => [
                "latitude" => 34.4234,
                "longitude" => 53.53245,
            ],
        ];

        $trip = Trip::fromArray($data, new MockUuid());
        self::assertEquals(
            expected: 1,
            actual: $trip->getStatus()->value,
        );
    }

    public function testFailsWhenInvalidDataProvided(): void
    {
        self::expectException(HydrationException::class);
        $data = [
            "id" => "568ec104-ffff-4980-a431-1c3d0cfd997f",
            "status" => 7,
            "source" => [
                "latitude" => 34.5234,
                "longitude" => 53.43245,
            ],
            "destination" => [
                "latitude" => 34.4234,
                "longitude" => 53.53245,
            ],
        ];

        $trip = Trip::fromArray($data, new MockUuid());
    }
}