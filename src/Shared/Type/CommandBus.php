<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface CommandBus
{
    /**
     * @throws HandlerNotFoundException
     */
    public function handle(Command $command): void;
}