<?php

declare(strict_types=1);

namespace App\Delivery\Driver;

enum Status: int
{
    case Free = 1;
    case Busy = 2;
    case OnHold = 3;
    case Away = 4;
    case Reserved = 5;
}