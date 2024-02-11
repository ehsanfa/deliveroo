<?php

declare(strict_types=1);

namespace Test\Integration\Delivery\Driver\Query;

use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\Query\GetDriverQuery;
use App\Delivery\Driver\Status;
use App\Shared\Type\QueryBus;
use App\Shared\Type\UuidGenerator;
use Test\Integration\Shared\TestWithCleanup;

class GetDriverQueryTest extends TestWithCleanup
{
    public function testGetDriver(): void
    {
        $uuidGenerator = $this->getContainer()->get(UuidGenerator::class);
        $driverId = new Id($uuidGenerator->generate());
        $driverCommandBus = $this->getContainer()->get('delivery.driver.command.bus');
        $command = new CreateDriverCommand(
            id: $driverId,
        );
        $driverCommandBus->handle($command);

        $queryBus = $this->getContainer()->get('delivery.driver.query.bus');
        $query = new GetDriverQuery($driverId);
        $driver = $queryBus->handle($query);

        self::assertEquals(
            expected: $driver->getStatus(),
            actual: Status::OnHold,
        );
    }
}