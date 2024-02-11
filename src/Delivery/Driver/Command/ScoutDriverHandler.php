<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\DriverList;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFreeException;
use App\Delivery\Driver\Exception\NoDriverAvailableException;
use App\Delivery\Driver\Query\GetFreeDriversAroundQuery;
use App\Delivery\Shared\Configuration\ConfigurationManager;
use App\Delivery\Shared\Exception\MissingConfigurationException;
use App\Delivery\Shared\Exception\TripNotFoundException;
use App\Delivery\Trip\Query\GetTripQuery;
use App\Shared\Type\HandlerNotFoundException;
use App\Shared\Type\QueryBus;

final readonly class ScoutDriverHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
        private ConfigurationManager $configurationManager,
        private QueryBus $driverQueryBus,
        private QueryBus $tripQueryBus,
    ) {
    }

    /**
     * @throws NoDriverAvailableException
     * @throws MissingConfigurationException
     * @throws TripNotFoundException
     * @throws HandlerNotFoundException
     */
    public function __invoke(ScoutDriverCommand $scoutDriverCommand): void
    {
        $tripId = $scoutDriverCommand->getTripId();
        $scorer = $scoutDriverCommand->getScorer();

        $trip = $this->tripQueryBus->handle(new GetTripQuery($tripId));

        if (null === $trip) {
            throw new TripNotFoundException();
        }

        /** @var DriverList $driversList */
        $driversList = $this->driverQueryBus->handle(
            new GetFreeDriversAroundQuery(
                location: $trip->getSource(),
                maxDistance: $this->configurationManager->scoutDriverMaxDistanceBikersAround(),
                lastActivityUntil: $this->configurationManager->scoutDriverLastActivityUntil(),
            ),
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