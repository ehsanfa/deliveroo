<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Status;

final readonly class CreateDriverHandler
{
    public function __construct(
        private DriverRepository $driverRepository,
    ) {
    }

    public function __invoke(CreateDriverCommand $command): void
    {
        $id = $command->getId() ?: $this->driverRepository->nextIdentity();
        $driver = Driver::create(
            id: $id,
            status: Status::OnHold
        );
        $this->driverRepository->create($driver);
    }
}