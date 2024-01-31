<?php

declare(strict_types=1);

namespace App\Shared\Type;

enum ComparisonResult
{
    case IsBiggerThan;
    case IsEqualTo;
    case IsSmallerThan;
}