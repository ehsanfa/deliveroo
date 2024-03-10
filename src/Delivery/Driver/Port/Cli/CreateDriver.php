<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Port\Cli;

use App\Delivery\Driver;
use App\Delivery\Driver\Command\CreateDriverCommand;
use App\Shared\Type\CommandBus;
use App\Shared\Type\UuidGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class CreateDriver extends Command
{
    public function __construct(
        private readonly UuidGenerator $uuidGenerator,
        private readonly CommandBus $driverCommandBus,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('delivery:driver:create')
            ->setDescription("Create a driver");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driverId = new Driver\Id($this->uuidGenerator->generate());
        $this->driverCommandBus->handle(new CreateDriverCommand($driverId));
        $output->writeln("Driver {$driverId->toString()} was created");

        return Command::SUCCESS;
    }
}