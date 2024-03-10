<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\Exception\DriverNotFreeException;
use App\Delivery\Driver\Query\GetDriverQuery;
use App\Delivery\Shared\Bus\QueryBus;
use App\Shared\Type\HandlerNotFoundException;

final readonly class MarkDriverReservedHandler
{
    public function __construct(
        private DriverRepository         $driverRepository,
        private QueryBus                 $driverQueryBus,
    ) {
    }

    /**
     * @throws DriverNotFoundException
     * @throws HandlerNotFoundException
     * @throws DriverNotFreeException
     */
    public function __invoke(MarkDriverReservedCommand $command): void
    {
        /** @var Driver $driver */
        $driver = $this->driverQueryBus->handle(
            new GetDriverQuery(
                $command->getDriverId()
            )
        );

        if (null === $driver) {
            throw new DriverNotFoundException();
        }

        $driver->reserveFor($command->getTripId());
        $this->driverRepository->update($driver);
    }
}