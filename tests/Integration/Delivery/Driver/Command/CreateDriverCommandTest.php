<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Command;

use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Status;
use App\Shared\Type\CommandBus;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Test\Integration\Shared\TestWithCleanup;
use Test\Unit\Delivery\Driver\MockUuid;

class CreateDriverCommandTest extends TestWithCleanup
{
    public function testCreateDriver(): void
    {
        $driverId = new Id((new MockUuid())->generate());
        /** @var CommandBus $bus */
        $bus = $this->getContainer()->get('delivery.driver.command.bus');
        $command = new CreateDriverCommand(
            id: $driverId,
        );
        $bus->handle($command);

        /** @var DriverRepository $driverRepository */
        $driverRepository = $this->getContainer()->get(DriverRepository::class);
        self::assertEquals(
            expected: Status::OnHold,
            actual: $driverRepository->getStatusById($driverId),
        );
    }
}