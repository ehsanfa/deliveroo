<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver\Command;

use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Delivery\Driver\Command\CreateDriverHandler;
use App\Delivery\Driver\Driver;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Status;
use PHPUnit\Framework\TestCase;
use Test\Unit\Delivery\Driver\MockUuid;

class CreateDriverCommandTest extends TestCase
{
    public function testCreateDriver(): void
    {
        $command = new CreateDriverCommand(
            id: new Id(
                id: MockUuid::fromString('driver-1'),
            ),
        );

        $driverRepository = $this->createMock(DriverRepository::class);
        $driverRepository
            ->expects(self::once())
            ->method('create')
            ->with(Driver::create(
                id: $command->getId(),
                status: Status::OnHold
            ));

        $handler = new CreateDriverHandler($driverRepository);

        $handler->__invoke($command);
    }
}