<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver\Event;

use App\Shared\Type\UuidValidator;
use PHPUnit\Framework\TestCase;

class DriverEventFactoryTest extends TestCase
{
    public function testCreateDriverAssignedEvent(): void
    {
        $tripId = "c6b91e0d-44ff-4790-9a0f-9ec2753fab3c";
        $driverId = "1fe442fe-d39b-4442-a5b0-0c3c98b7102c";

        $uuidValidator = $this->createMock(UuidValidator::class);
        $uuidValidator->method('isValid')
            ->willReturn(true);

        $factory = new UuidIdentifierDriverEventFactory($uuidValidator);
        $tripCreatedEvent = $factory->createDriverAssignedEvent(
            tripId: $tripId,
            driverId: $driverId,
        );
    }
}