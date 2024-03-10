<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Port\Cli;

use App\Delivery\Driver;
use App\Delivery\Driver\Command\UpdateDriverLocationCommand;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Location;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class UpdateLocation extends Command
{
    public function __construct(
        private readonly CommandBus $driverCommandBus,
        private readonly UuidValidator $uuidValidator,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('delivery:driver:location:update')
            ->setDescription('Update a driver\'s location')
            ->addArgument(
                name: 'driverId',
                mode: InputArgument::REQUIRED,
                description: 'Driver Id',
            )
            ->addArgument(
                name: 'latitude',
                mode: InputArgument::REQUIRED,
                description: 'Driver latitude',
            )
            ->addArgument(
                name: 'longitude',
                mode: InputArgument::REQUIRED,
                description: 'Driver longitude',
            )
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driverId = new Driver\Id(Uuid::fromString(
            string: $input->getArgument('driverId'),
            validator: $this->uuidValidator
        ));
        $latitude = (float)$input->getArgument('latitude');
        $longitude = (float)$input->getArgument('longitude');

        $this->driverCommandBus->handle(new UpdateDriverLocationCommand(
            $driverId,
            new Location($latitude, $longitude)
        ));

        return Command::SUCCESS;
    }
}