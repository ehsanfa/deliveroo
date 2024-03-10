<?php

declare(strict_types=1);

namespace App\Delivery\Driver\Port\Cli;

use App\Delivery\Driver;
use App\Delivery\Driver\Command\MarkDriverFreeCommand;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class MarkAsFree extends Command
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
            ->setName('delivery:driver:status:free')
            ->setDescription('Update a driver\'s status')
            ->addArgument(
                name: 'driverId',
                mode: InputArgument::REQUIRED,
                description: 'Driver Id',
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driverId = new Driver\Id(Uuid::fromString(
            string: $input->getArgument('driverId'),
            validator: $this->uuidValidator
        ));

        $this->driverCommandBus->handle(new MarkDriverFreeCommand(
            $driverId,
        ));

        return Command::SUCCESS;
    }
}