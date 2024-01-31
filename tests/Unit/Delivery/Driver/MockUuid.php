<?php

declare(strict_types=1);

namespace Test\Unit\Delivery\Driver;

use App\Shared\Type\InvalidUuidException;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidGenerator;
use App\Shared\Type\UuidValidator;

readonly class MockUuid implements UuidGenerator, UuidValidator
{
    /**
     * @throws InvalidUuidException
     */
    public function generate(): Uuid
    {
        return Uuid::fromString(
            sprintf('018c310d-cb9a-7118-8641-%d', rand(100000000000, 999999999999)),
            validator: $this,
        );
    }

    public function isValid(string $uuid): bool
    {
        return true;
    }

    public static function fromString(string $uuid): Uuid
    {
        return Uuid::fromString($uuid, new self());
    }
}