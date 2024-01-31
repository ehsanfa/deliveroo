<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Configuration;

use App\Delivery\Shared\Exception\MissingConfigurationException;
use App\Shared\Distance\Distance;

final readonly class ConfigurationManagerWithFallback implements ConfigurationManager
{
    public function __construct(
        private ConfigurationManager $mainConfigurationManager,
        private ConfigurationManager $fallbackConfigurationManager,
    ) {
    }

    public function scoutDriverMaxDistanceBikersAround(): Distance
    {
        try {
            return $this->mainConfigurationManager->scoutDriverMaxDistanceBikersAround();
        } catch (MissingConfigurationException) {
            return $this->fallbackConfigurationManager->scoutDriverMaxDistanceBikersAround();
        }
    }

    public function scoutDriverLastActivityUntil(): \DateTimeImmutable
    {
        try {
            return $this->mainConfigurationManager->scoutDriverLastActivityUntil();
        } catch (MissingConfigurationException) {
            return $this->fallbackConfigurationManager->scoutDriverLastActivityUntil();
        }
    }

    public function scoutDriverRateScoreWeight(): int
    {
        try {
            return $this->mainConfigurationManager->scoutDriverRateScoreWeight();
        } catch (MissingConfigurationException) {
            return $this->fallbackConfigurationManager->scoutDriverRateScoreWeight();
        }
    }

    public function scoutDriverRookieScoreWeight(): int
    {
        try {
            return $this->mainConfigurationManager->scoutDriverRookieScoreWeight();
        } catch (MissingConfigurationException) {
            return $this->fallbackConfigurationManager->scoutDriverRookieScoreWeight();
        }
    }
}