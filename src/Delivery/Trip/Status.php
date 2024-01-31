<?php

declare(strict_types=1);

namespace App\Delivery\Trip;

enum Status: int
{
    case Open = 1;
    case PendingAcceptance = 2;
    case InProgress = 3;
    case Finished = 4;
}