<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Configuration;

use App\Shared\Distance\Distance;
use App\Shared\Distance\Unit;

final readonly class DefaultValuesConfigurationManager implements ConfigurationManager
{
    public function scoutDriverMaxDistanceBikersAround(): Distance
    {
        return new Distance(5, Unit::Kilometer);
    }

    public function scoutDriverLastActivityUntil(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('-10 minutes');
    }

    public function scoutDriverRateScoreWeight(): int
    {
        return 5;
    }

    public function scoutDriverRookieScoreWeight(): int
    {
        return 100;
    }
}