<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface EventDispatcher
{
    public function dispatch(DomainEvent $domainEvent): void;
}