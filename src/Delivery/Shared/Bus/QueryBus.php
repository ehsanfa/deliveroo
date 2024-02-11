<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Bus;

use App\Shared\Type\HandlerNotFoundException;
use App\Shared\Type\Query;
use App\Shared\Type\QueryBus as SharedQueryBus;

final readonly class QueryBus implements SharedQueryBus
{
    /**
     * @param array<Query, object> $handlers
     */
    public function __construct(
        private array $handlers,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function handle(Query $query): ?object
    {
        $handler = $this->handlers[$query::class] ?? null;
        if (null === $handler) {
            throw new HandlerNotFoundException();
        }

        return $handler->__invoke($query);
    }
}