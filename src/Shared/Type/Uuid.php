<?php

declare(strict_types=1);

namespace App\Shared\Type;

readonly class Uuid
{
    private function __construct(
        private string $uuid,
    ) {
    }

    /**
     * @throws InvalidUuidException
     */
    public static function fromString(string $string, UuidValidator $validator): Uuid
    {
        if (!$validator->isValid($string)) {
            throw new InvalidUuidException();
        }

        return new self($string);
    }

    public function toString(): string
    {
        return $this->uuid;
    }
}