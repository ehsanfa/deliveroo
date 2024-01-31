<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\Id;
use App\Shared\Type\Command;

readonly class CreateDriverCommand implements Command
{
    public function __construct(
        private Id $id,
    ) {
    }

    public function getId(): Id
    {
        return $this->id;
    }
}