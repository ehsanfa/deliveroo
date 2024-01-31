<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Configuration;

use App\Delivery\Shared\Exception\MissingConfigurationException;
use App\Shared\Distance\Distance;

interface ConfigurationManager
{
    /**
     * @throws MissingConfigurationException
     */
    public function scoutDriverMaxDistanceBikersAround(): Distance;

    /**
     * @throws MissingConfigurationException
     */
    public function scoutDriverLastActivityUntil(): \DateTimeImmutable;

    /**
     * @throws MissingConfigurationException
     */
    public function scoutDriverRateScoreWeight(): int;

    /**
     * @throws MissingConfigurationException
     */
    public function scoutDriverRookieScoreWeight(): int;
}