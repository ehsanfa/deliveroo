<?php

declare(strict_types=1);

namespace App\Geo\Shared\Bus;

use App\Shared\Type\Command;
use App\Shared\Type\CommandBus as SharedCommandBus;
use App\Shared\Type\HandlerNotFoundException;

final readonly class CommandBus implements SharedCommandBus
{
    /**
     * @param array<Command, object> $handlers
     */
    public function __construct(
        private array $handlers,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(Command $command): void
    {
        $handler = $this->handlers[$command::class] ?? null;
        if (null === $handler) {
            throw new HandlerNotFoundException();
        }

        $handler->__invoke($command);
    }
}