<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Command;

use App\Delivery\Driver\Command\ScoutDriverCommand;
use App\Delivery\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\NoDriverAvailableException;
use App\Delivery\Driver\Scorer;
use App\Delivery\Trip\Command\CreateTripCommand;
use App\Delivery\Trip\Id;
use App\Delivery\Trip\Status;
use App\Delivery\Trip\Trip;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Location;
use App\Shared\Type\QueryBus;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class ScoutDriverCommandTest extends TestWithCleanup
{
    private UuidGenerator $uuidGenerator;
    private Scorer $rateScorer;
    private Scorer $rookieScorer;
    private CommandBus $driverCommandBus;
    private CommandBus $tripCommandBus;
    private DriverRepository $driverRepository;

    protected function setUp(): void
    {
        $this->uuidGenerator = self::getContainer()->get(UuidGenerator::class);
        $this->rookieScorer = self::getContainer()->get('delivery.scout.scorer.rookie');
        $this->rateScorer = self::getContainer()->get('delivery.scout.scorer.rate');
        $this->driverCommandBus = self::getContainer()->get('delivery.driver.command.bus');
        $this->tripCommandBus = self::getContainer()->get('delivery.trip.command.bus');
        $this->driverRepository = self::getContainer()->get(DriverRepository::class);
        parent::setUp();
    }

    public function testThrowsExceptionWhenNoDriverAvailable(): void
    {
        self::expectException(NoDriverAvailableException::class);
        $tripId = new Id($this->uuidGenerator->generate());

        $createTripCommand = new CreateTripCommand(
            id: $tripId,
            source: new Location(latitude: 34.234235, longitude: 53.3423),
            destination: new Location(latitude: 34.542345, longitude: 53.34235),
        );
        $this->tripCommandBus->handle($createTripCommand);

        $scorer = new Scorer\MultipleScorer(
            $this->rateScorer,
            $this->rookieScorer,
        );
        $command = new ScoutDriverCommand(
            tripId: $tripId,
            scorer: $scorer,
        );

        $this->driverCommandBus->handle($command);
    }

    public function testSuccessfulScout(): void
    {
        $tripId = new Id($this->uuidGenerator->generate());
        $driverId = new Driver\Id($this->uuidGenerator->generate());
        $tripSource = new Location(latitude: 34.234235, longitude: 53.3423);

        $this->tripCommandBus->handle(new CreateTripCommand(
            id: $tripId,
            source: $tripSource,
            destination: new Location(latitude: 34.542345, longitude: 53.34235),
        ));

        $this->driverCommandBus->handle(new Driver\Command\CreateDriverCommand($driverId));

        $this->driverCommandBus->handle(new Driver\Command\MarkDriverFreeCommand($driverId));

        $this->driverCommandBus->handle(new Driver\Command\UpdateDriverLocationCommand(
            driverId: $driverId,
            location: $tripSource,
        ));

        $scorer = new Scorer\MultipleScorer(
            $this->rateScorer,
            $this->rookieScorer,
        );
        $this->driverCommandBus->handle(new ScoutDriverCommand(
            tripId: $tripId,
            scorer: $scorer,
        ));

        $reservedDrivers = $this->driverRepository->getReservedDriversForTrip($tripId);

        self::assertEquals(
            expected: Driver\Status::Reserved,
            actual: $this->driverRepository->getStatusById($driverId),
        );

        self::assertFalse(
            $reservedDrivers->isEmpty()
        );
    }
}