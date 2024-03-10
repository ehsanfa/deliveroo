<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\Id;
use App\Shared\Type\Command;

final readonly class CreateDriverCommand implements Command
{
    private ?Id $id;

    public function __construct(
    ) {
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function setId(Id $id): void
    {
        $this->id = $id;
    }
}