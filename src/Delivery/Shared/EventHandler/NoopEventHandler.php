<?php

declare(strict_types=1);

namespace App\Delivery\Shared\EventHandler;

final readonly class NoopEventHandler
{
    public function handle(object $event): void
    {
    }
}