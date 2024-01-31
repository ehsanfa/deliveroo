<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Command;

use App\Delivery\Driver\Command\ScoutDriverCommand;
use App\Delivery\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\NoDriverAvailableException;
use App\Delivery\Driver\Scorer;
use App\Delivery\Trip\Id;
use App\Delivery\Trip\Status;
use App\Delivery\Trip\Trip;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Location;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class ScoutDriverCommandTest extends TestWithCleanup
{
    private UuidGenerator $uuidGenerator;
    private Scorer $rateScorer;
    private Scorer $rookieScorer;
    private CommandBus $commandBus;
    private DriverRepository $driverRepository;

    protected function setUp(): void
    {
        $this->uuidGenerator = self::getContainer()->get(UuidGenerator::class);
        $this->rookieScorer = self::getContainer()->get('delivery.scout.scorer.rookie');
        $this->rateScorer = self::getContainer()->get('delivery.scout.scorer.rate');
        $this->commandBus = self::getContainer()->get('delivery.driver.command.bus');
        $this->driverRepository = self::getContainer()->get(DriverRepository::class);
        parent::setUp();
    }

    public function testThrowsExceptionWhenNoDriverAvailable(): void
    {
        self::expectException(NoDriverAvailableException::class);
        $trip = Trip::create(
            id: new Id($this->uuidGenerator->generate()),
            status: Status::Open,
            source: new Location(latitude: 34.234235, longitude: 53.3423),
            destination: new Location(latitude: 34.542345, longitude: 53.34235),
        );
        $scorer = new Scorer\MultipleScorer(
            $this->rateScorer,
            $this->rookieScorer,
        );
        $command = new ScoutDriverCommand(
            trip: $trip,
            scorer: $scorer,
        );

        $this->commandBus->handle($command);
    }

    public function testSuccessfulScout(): void
    {
        $trip = Trip::create(
            id: new Id($this->uuidGenerator->generate()),
            status: Status::Open,
            source: new Location(latitude: 34.234235, longitude: 53.3423),
            destination: new Location(latitude: 34.542345, longitude: 53.34235),
        );
        $scorer = new Scorer\MultipleScorer(
            $this->rateScorer,
            $this->rookieScorer,
        );
        $command = new ScoutDriverCommand(
            trip: $trip,
            scorer: $scorer,
        );

        $driver1 = Driver\Driver::create(
            id: new Driver\Id($this->uuidGenerator->generate()),
            status: Driver\Status::Free,
        );
        $driver1->setLocation(new Location(latitude: 34.234235, longitude: 53.34237));
        $driver1->setLastLocationUpdateAt(new \DateTimeImmutable());
        $this->driverRepository->create($driver1);

        $this->commandBus->handle($command);

        self::assertEquals(
            expected: $this->driverRepository->getStatusById($driver1->getId()),
            actual: Driver\Status::Reserved,
        );
    }
}