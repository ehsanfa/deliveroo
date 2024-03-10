<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Port\Cli;

use App\Delivery\Trip\Command\CreateTripCommand;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Location;
use App\Shared\Type\UuidGenerator;
use App\Delivery\Trip;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateTrip extends Command
{
    public function __construct(
        private readonly UuidGenerator $uuidGenerator,
        private readonly CommandBus $tripCommandBus,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('delivery:trip:create')
            ->setDescription("Create a trip")
            ->addArgument('source_latitude')
            ->addArgument('source_longitude')
            ->addArgument('destination_latitude')
            ->addArgument('destination_longitude')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tripId = new Trip\Id($this->uuidGenerator->generate());
        $sourceLatitude = (float)$input->getArgument('source_latitude');
        $sourceLongitude = (float)$input->getArgument('source_longitude');
        $destinationLatitude = (float)$input->getArgument('destination_latitude');
        $destinationLongitude = (float)$input->getArgument('destination_longitude');

        $this->tripCommandBus->handle(new CreateTripCommand(
            $tripId,
            source: new Location($sourceLatitude, $sourceLongitude),
            destination: new Location($destinationLatitude, $destinationLongitude),
        ));
        $output->writeln("Trip {$tripId->toString()} was created");

        return Command::SUCCESS;
    }
}