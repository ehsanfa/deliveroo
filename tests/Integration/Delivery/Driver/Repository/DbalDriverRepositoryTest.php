<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Repository;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidGenerator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DbalDriverRepositoryTest extends KernelTestCase
{
    private readonly DriverRepository $driverRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->driverRepository = $this->getContainer()->get(DriverRepository::class);
    }

    public function testCreate(): void
    {
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $driver = Driver::create(
            id: new Id($uuidGenerator->generate()),
            status: Status::Free,
        );

        $this->driverRepository->create($driver);

        self::assertEquals(
            expected: $driver->getStatus()->value,
            actual: $this->driverRepository->getStatusById($driver->getId())?->value,
        );
    }

    public function testUpdate(): void
    {
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $driver = Driver::create(
            id: new Id($uuidGenerator->generate()),
            status: Status::Free,
        );

        /** @var DriverRepository $driverRepository */
        $driverRepository = $this->getContainer()->get(DriverRepository::class);
        $driverRepository->create($driver);

        $tripId = new \App\Delivery\Trip\Id($uuidGenerator->generate());

        $driver->reserveFor($tripId);

        $driverRepository->update($driver);

        self::assertEquals(
            expected: $driver->getStatus()->value,
            actual: $driverRepository->getStatusById($driver->getId())?->value,
        );
    }

    public function testDelete(): void
    {
        self::expectException(DriverNotFoundException::class);
        /** @var UuidGenerator $uuidGenerator */
        $uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $driver = Driver::create(
            id: new Id($uuidGenerator->generate()),
            status: Status::Free,
        );

        /** @var DriverRepository $driverRepository */
        $driverRepository = $this->getContainer()->get(DriverRepository::class);
        $driverRepository->create($driver);

        $driverRepository->delete($driver);

        $driverRepository->getStatusById($driver->getId());
    }
}