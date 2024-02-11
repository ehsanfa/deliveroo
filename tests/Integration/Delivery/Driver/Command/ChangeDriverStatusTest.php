<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Command;

use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Delivery\Driver\Command\MarkDriverBusyCommand;
use App\Delivery\Driver\Command\MarkDriverFreeCommand;
use App\Delivery\Driver\Command\ScoutDriverCommand;
use App\Delivery\Driver\Command\UpdateDriverLocationCommand;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Query\GetDriverQuery;
use App\Delivery\Driver\Scorer;
use App\Delivery\Driver\Status;
use App\Delivery\Trip;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Location;
use App\Shared\Type\QueryBus;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class ChangeDriverStatusTest extends TestWithCleanup
{
    private UuidGenerator $uuidGenerator;
    private CommandBus $driverCommandBus;
    private CommandBus $tripCommandBus;
    private QueryBus $driverQueryBus;
    private Scorer $rookieScorer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $this->driverCommandBus = $this->getContainer()->get('delivery.driver.command.bus');
        $this->tripCommandBus = $this->getContainer()->get('delivery.trip.command.bus');
        $this->driverQueryBus = $this->getContainer()->get('delivery.driver.query.bus');
        $this->rookieScorer = $this->getContainer()->get('delivery.scout.scorer.rookie');
    }

    public function testMarkDriverAsBusy(): void
    {
        $driverId = new Id($this->uuidGenerator->generate());
        $source = new Location(latitude: 45.324, longitude: 54.234);
        $destination = new Location(latitude: 45.324, longitude: 54.234);
        $tripId = new Trip\Id($this->uuidGenerator->generate());

        $this->driverCommandBus->handle(new CreateDriverCommand(
            id: $driverId,
        ));

        $this->tripCommandBus->handle(new Trip\Command\CreateTripCommand(
            id: $tripId,
            source: $source,
            destination: $destination,
        ));

        $this->driverCommandBus->handle(new UpdateDriverLocationCommand(
            driverId: $driverId,
            location: $source,
        ));

        $this->driverCommandBus->handle(new MarkDriverFreeCommand($driverId));

        $this->driverCommandBus->handle(new ScoutDriverCommand(
            tripId: $tripId,
            scorer: $this->rookieScorer,
        ));

        $this->driverCommandBus->handle(new MarkDriverBusyCommand(
            driverId: $driverId,
            tripId: $tripId,
        ));

        $driver = $this->driverQueryBus->handle(new GetDriverQuery($driverId));

        self::assertEquals(
            expected: Status::Busy,
            actual: $driver->getStatus(),
        );
    }
}