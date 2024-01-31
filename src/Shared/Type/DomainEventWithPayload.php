<?php

namespace App\Shared\Type;

interface DomainEventWithPayload extends DomainEvent
{
    public function getPayload(): array;
}