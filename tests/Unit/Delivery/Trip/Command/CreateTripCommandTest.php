<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Trip\Command;

use App\Delivery\Trip\Command\CreateTripCommand;
use App\Delivery\Trip\Command\CreateTripHandler;
use App\Delivery\Trip\Id;
use App\Delivery\Trip\PersistingTripRepository;
use App\Shared\Type\Location;
use PHPUnit\Framework\TestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class CreateTripCommandTest extends TestCase
{
    public function testCreateTrip(): void
    {
        $command = new CreateTripCommand(
            id: new Id(
                id: MockUuid::fromString('driver-1'),
            ),
            source: new Location(
                latitude: 34.454353,
                longitude: 53.2313,
            ),
            destination: new Location(
                latitude: 34.454353,
                longitude: 53.2313,
            ),
        );

        $handler = new CreateTripHandler(
            persistingTripRepository: $this->createMock(PersistingTripRepository::class)
        );
        $trip = $handler->__invoke($command);

        self::assertTrue(
            $trip->isFresh(),
        );
    }
}