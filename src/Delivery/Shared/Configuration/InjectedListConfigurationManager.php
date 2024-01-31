<?php

declare(strict_types=1);

namespace App\Delivery\Shared\Configuration;

use App\Delivery\Shared\Exception\MissingConfigurationException;
use App\Shared\Distance\Distance;

final readonly class InjectedListConfigurationManager implements ConfigurationManager
{
    public function __construct(
        private array $configs,
    ) {
    }

    /**
     * @throws MissingConfigurationException
     */
    public function scoutDriverMaxDistanceBikersAround(): Distance
    {
        if (!isset($this->configs['scout']['maxDistanceValue'])
            || !isset($this->configs['scout']['maxDistanceUnit'])
        ) {
            throw new MissingConfigurationException();
        }

        return new Distance(
            value: (int)$this->configs['scout']['maxDistanceValue'],
            unit: $this->configs['scout']['maxDistanceUnit'],
        );
    }

    /**
     * @throws MissingConfigurationException
     * @throws \Exception
     */
    public function scoutDriverLastActivityUntil(): \DateTimeImmutable
    {
        if (!isset($this->configs['scout']['lastActivityUntil'])) {
            throw new MissingConfigurationException();
        }

        return new \DateTimeImmutable($this->configs['scout']['lastActivityUntil']);
    }

    public function scoutDriverRateScoreWeight(): int
    {
        if (!isset($this->configs['scout']['rateScoreWeight'])) {
            throw new MissingConfigurationException();
        }
        return (int)$this->configs['scout']['rateScoreWeight'];
    }

    public function scoutDriverRookieScoreWeight(): int
    {
        if (!isset($this->configs['scout']['rookieScoreWeight'])) {
            throw new MissingConfigurationException();
        }
        return (int)$this->configs['scout']['rookieScoreWeight'];
    }
}