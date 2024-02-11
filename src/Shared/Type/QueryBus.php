<?php

declare(strict_types=1);

namespace App\Shared\Type;

interface QueryBus
{
    /**
     * @throws HandlerNotFoundException
     */
    public function handle(Query $query): ?object;
}
