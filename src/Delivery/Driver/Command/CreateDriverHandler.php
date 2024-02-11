<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Command;

use App\Delivery\Driver\Driver;
use App\Delivery\Driver\DriverRepository;
use App\Delivery\Driver\Status;

final readonly class CreateDriverHandler
{
    public function __construct(
        private DriverRepository $persistingDriverRepository,
    ) {
    }

    public function __invoke(CreateDriverCommand $command): void
    {
        $driver = Driver::create(
            id: $command->getId(),
            status: Status::OnHold
        );
        $this->persistingDriverRepository->create($driver);
    }
}