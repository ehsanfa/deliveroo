<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFreeException;
use App\Delivery\Driver\Exception\NoDriverAvailableException;
use App\Delivery\Shared\Configuration\ConfigurationManager;
use App\Delivery\Shared\Exception\MissingConfigurationException;

readonly class ScoutDriverHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
        private ConfigurationManager $configurationManager,
    ) {
    }

    /**
     * @throws NoDriverAvailableException
     * @throws MissingConfigurationException
     */
    public function __invoke(ScoutDriverCommand $scoutDriverCommand): void
    {
        $trip = $scoutDriverCommand->getTrip();
        $scorer = $scoutDriverCommand->getScorer();

        $driversList = $this->driverRepository->getFreeDriversAround(
            location: $trip->getSource(),
            distance: $this->configurationManager->scoutDriverMaxDistanceBikersAround(),
            lastActivityUntil: $this->configurationManager->scoutDriverLastActivityUntil(),
        );

        if ($driversList->isEmpty()) {
            throw new NoDriverAvailableException();
        }

        $driversList = $driversList->sortByScorer($scorer);

        foreach ($driversList as $driver) {
            try {
                $driver->reserveFor($trip->getId());
                $this->driverRepository->update($driver);
                return;
            } catch (DriverNotFreeException $e) {
                continue;
            }
        }

        throw new NoDriverAvailableException();
    }
}