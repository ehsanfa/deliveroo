<?php

namespace App\Delivery\Shared;

use App\Delivery\Shared\Exception\UndefinedDomainEventException;
use App\Shared\Type\DomainEvent;
use JetBrains\PhpStorm\ArrayShape;

interface DomainEventFactory
{
    #[ArrayShape([
        'event' => 'string',
        'payload' => 'string',
    ])]
    /**
     * @throws UndefinedDomainEventException
     */
    public function getDomainEvent(array $data): DomainEvent;
}