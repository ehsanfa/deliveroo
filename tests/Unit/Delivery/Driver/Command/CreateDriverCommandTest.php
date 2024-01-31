<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver\Command;

use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Delivery\Driver\Command\CreateDriverHandler;
use App\Delivery\Driver\Id;
use App\Delivery\Driver\DriverRepository;
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

        $handler = new CreateDriverHandler(
            $this->createMock(DriverRepository::class),
        );

        $driver = $handler->__invoke($command);

        self::assertTrue(
            $driver->isFresh(),
        );
    }
}