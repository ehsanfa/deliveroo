<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Exception\DriverNotFoundException;
use App\Delivery\Driver\Query\GetDriverQuery;
use App\Delivery\Shared\Bus\QueryBus;
use App\Shared\Type\HandlerNotFoundException;

final readonly class MarkDriverFreeHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
        private QueryBus $driverQueryBus,
    ) {
    }

    /**
     * @throws DriverNotFoundException
     * @throws HandlerNotFoundException
     */
    public function __invoke(MarkDriverFreeCommand $command): void
    {
        $driver = $this->driverQueryBus->handle(new GetDriverQuery(
            driverId: $command->getDriverId(),
        ));
        if (null === $driver) {
            throw new DriverNotFoundException();
        }
        $driver->markAsFree();
        $this->driverRepository->update($driver);
    }
}