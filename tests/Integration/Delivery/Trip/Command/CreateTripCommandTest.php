<?php

declare(strict_types=1);

namespace Integration\Delivery\Trip\Command;

use App\Delivery\Shared\Bus\CommandBus;
use App\Delivery\Trip\Command\CreateTripCommand;
use App\Delivery\Trip\Id;
use App\Delivery\Trip\PersistingTripRepository;
use App\Shared\Type\Location;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class CreateTripCommandTest extends KernelTestCase
{
    public function testCreateTrip(): void
    {
        $tripId = new Id((new MockUuid())->generate());
        $source = new Location(latitude: 34.3532, longitude: 54.34234);
        $destination = new Location(latitude: 34.2532, longitude: 54.14234);
        /** @var CommandBus $bus */
        $bus = $this->getContainer()->get('delivery.trip.command.bus');
        $command = new CreateTripCommand(
            id: $tripId,
            source: $source,
            destination: $destination,
        );
        $bus->handle($command);

        /** @var PersistingTripRepository $tripRepository */
        $tripRepository = $this->getContainer()->get(PersistingTripRepository::class);
        self::assertNotNull($tripRepository->find($tripId));
    }
}