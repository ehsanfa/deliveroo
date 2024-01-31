<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface DomainEvent
{
    public function getAggregateRootId(): Id;

    public static function getIdentifier(): string;
}