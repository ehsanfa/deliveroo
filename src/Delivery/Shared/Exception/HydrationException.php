<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Exception;

use Throwable;

class HydrationException extends \Exception
{
    public function __construct(string $field)
    {
        parent::__construct(sprintf("%s doesn't exist or is invalid", $field));
    }
}