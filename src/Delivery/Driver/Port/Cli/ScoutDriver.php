<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Port\Cli;

use App\Delivery\Driver\Command\ScoutDriverCommand;
use App\Delivery\Driver\Scorer;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;
use App\Delivery\Trip;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ScoutDriver extends Command
{
    public function __construct(
        private readonly CommandBus $driverCommandBus,
        private readonly UuidValidator $uuidValidator,
        private readonly Scorer $scorer,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('delivery:driver:scout')
            ->setDescription('Scout drivers for a trip')
            ->addArgument(
                name: 'tripId',
                mode: InputArgument::REQUIRED,
                description: 'Trip Id',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tripId = new Trip\Id(Uuid::fromString(
            string: $input->getArgument('tripId'),
            validator: $this->uuidValidator,
        ));

        $this->driverCommandBus->handle(new ScoutDriverCommand(
            tripId: $tripId,
            scorer: $this->scorer,
        ));

        return Command::SUCCESS;
    }
}