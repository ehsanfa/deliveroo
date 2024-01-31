<?php

namespace Test\Unit\Delivery\Driver;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Scorer\MultipleScorer;
use App\Delivery\Driver\Scorer\RateScorer;
use App\Delivery\Driver\Scorer\RookieScorer;
use App\Delivery\Driver\Status;
use App\Delivery\DriverRate\DriverList;
use App\Delivery\DriverRate\DriverRate;
use App\Delivery\DriverRate\DriverRateList;
use App\Delivery\DriverRate\DriverRateRepository;
use App\Delivery\Trip\ReadOnlyTripRepository;
use PHPUnit\Framework\TestCase;

class SortingTest extends TestCase
{
    public function testSortingByScore(): void
    {
        $driverA = Driver::create(
            id: new Id(MockUuid::fromString('A')),
            status: Status::Free
        );
        $driverB = Driver::create(
            id: new Id(MockUuid::fromString('B')),
            status: Status::Free
        );
        $driverC = Driver::create(
            id: new Id(MockUuid::fromString('C')),
            status: Status::Free
        );
        $driverList = new DriverList([
            $driverA,
            $driverB,
            $driverC,
        ]);

        $driverRateRepositoryMock = $this->createStub(DriverRateRepository::class);
        $driverRateRepositoryMock->method('getRateByDrivers')
            ->willReturnCallback(function(DriverList $driverList) {
                return new DriverRateList([
                    new DriverRate(
                        driverId: new Id(MockUuid::fromString('A')),
                        rate: 4.6,
                    ),
                    new DriverRate(
                        driverId: new Id(MockUuid::fromString('B')),
                        rate: 4.3,
                    ),
                    new DriverRate(
                        driverId: new Id(MockUuid::fromString('C')),
                        rate: 4.9,
                    ),
                ]);
            });

        $rateScoring = new RateScorer(
            weight: 5,
            driverRateRepository: $driverRateRepositoryMock
        );

        $sortedDrivers = $driverList->sortByScorer($rateScoring);
        $driversList = [];
        foreach ($sortedDrivers as $driver) {
            $driversList[] = $driver;
        }

        self::assertEquals(
            expected: 'C',
            actual: $driversList[0]->getId()->toString(),
        );
        self::assertEquals(
            expected: 'A',
            actual: $driversList[1]->getId()->toString(),
        );
        self::assertEquals(
            expected: 'B',
            actual: $driversList[2]->getId()->toString(),
        );
    }

    public function testSortingByIsRookie(): void
    {
        $driverA = Driver::create(
            id: new Id(MockUuid::fromString('A')),
            status: Status::Free
        );
        $driverB = Driver::create(
            id: new Id(MockUuid::fromString('B')),
            status: Status::Free
        );
        $driverC = Driver::create(
            id: new Id(MockUuid::fromString('C')),
            status: Status::Free
        );
        $driverList = new DriverList([
            $driverA,
            $driverB,
            $driverC,
        ]);

        $tripRateRepositoryMock = $this->createStub(ReadOnlyTripRepository::class);
        $tripRateRepositoryMock->method('driverHasDoneMoreTripsThan')
            ->willReturnCallback(function(Driver $driver) {
                return match ($driver->getId()->toString()) {
                    'A', 'C' => true,
                    'B' => false,
                    default => throw new \Exception('Unexpected match value')
                };
            });

        $rookieScoring = new RookieScorer(
            weight: 100,
            tripRepository: $tripRateRepositoryMock
        );

        $sortedDrivers = $driverList->sortByScorer($rookieScoring);
        $driversList = [];
        foreach ($sortedDrivers as $driver) {
            $driversList[] = $driver;
        }

        self::assertEquals(
            expected: 'B',
            actual: $driversList[0]->getId()->toString(),
        );
        self::assertEquals(
            expected: 'A',
            actual: $driversList[1]->getId()->toString(),
        );
        self::assertEquals(
            expected: 'C',
            actual: $driversList[2]->getId()->toString(),
        );
    }

    public function testScoringWithMultipleScorers(): void
    {
        $driverA = Driver::create(
            id: new Id(MockUuid::fromString('A')),
            status: Status::Free
        );
        $driverB = Driver::create(
            id: new Id(MockUuid::fromString('B')),
            status: Status::Free
        );
        $driverC = Driver::create(
            id: new Id(MockUuid::fromString('C')),
            status: Status::Free
        );
        $driverList = new DriverList([
            $driverA,
            $driverB,
            $driverC,
        ]);

        $tripRateRepositoryMock = $this->createStub(ReadOnlyTripRepository::class);
        $tripRateRepositoryMock->method('driverHasDoneMoreTripsThan')
            ->willReturnCallback(function(Driver $driver) {
                return match ($driver->getId()->toString()) {
                    'A', 'C' => true,
                    'B' => false,
                    default => throw new \Exception('Unexpected match value')
                };
            });

        $rookieScorer = new RookieScorer(
            weight: 100,
            tripRepository: $tripRateRepositoryMock,
        );

        $driverRateRepositoryMock = $this->createStub(DriverRateRepository::class);
        $driverRateRepositoryMock->method('getRateByDrivers')
            ->willReturnCallback(function(DriverList $driverList) {
                return new DriverRateList([
                    new DriverRate(
                        driverId: new Id(MockUuid::fromString('A')),
                        rate: 4.6,
                    ),
                    new DriverRate(
                        driverId: new Id(MockUuid::fromString('B')),
                        rate: 4.3,
                    ),
                    new DriverRate(
                        driverId: new Id(MockUuid::fromString('C')),
                        rate: 4.9,
                    ),
                ]);
            });

        $rateScorer = new RateScorer(
            weight: 5,
            driverRateRepository: $driverRateRepositoryMock
        );

        $sortedDrivers = $driverList->sortByScorer(new MultipleScorer(
            $rookieScorer,
            $rateScorer,
        ));
        $driversList = [];
        foreach ($sortedDrivers as $driver) {
            $driversList[] = $driver;
        }

        self::assertEquals(
            expected: 'B',
            actual: $driversList[0]->getId()->toString(),
        );
        self::assertEquals(
            expected: 'C',
            actual: $driversList[1]->getId()->toString(),
        );
        self::assertEquals(
            expected: 'A',
            actual: $driversList[2]->getId()->toString(),
        );
    }
}