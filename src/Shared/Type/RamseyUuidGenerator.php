<?php

namespace App\Shared\Type;

final readonly class RamseyUuidGenerator implements UuidGenerator
{
    public function __construct(
        private UuidValidator $validator,
    ) {
    }

    /**
     * @throws InvalidUuidException
     */
    public function generate(): Uuid
    {
        return Uuid::fromString(
            string: \Ramsey\Uuid\Uuid::uuid7()->toString(),
            validator: $this->validator,
        );
    }
}