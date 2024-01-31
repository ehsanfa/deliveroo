<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface DomainEventHandler
{
    public function handle(DomainEvent $domainEvent): void;
}