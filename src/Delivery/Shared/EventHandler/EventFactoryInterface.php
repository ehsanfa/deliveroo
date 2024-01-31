<?php

declare(strict_types=1);

namespace App\Delivery\Shared\EventHandler;

use App\Shared\Type\DomainEvent;

interface EventFactoryInterface
{
    public function getDomainEvent(string $identifier): DomainEvent;
}