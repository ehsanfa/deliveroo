<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Port\Cli;

use App\Delivery\Trip\Command\MarkTripAsDeliveredCommand;
use App\Shared\Type\CommandBus;
use App\Shared\Type\Uuid;
use App\Shared\Type\UuidValidator;
use App\Delivery\Trip;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TripDelivered extends Command
{
    public function __construct(
        private readonly CommandBus $tripCommandBus,
        private readonly UuidValidator $uuidValidator,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('delivery:trip:delivered')
            ->setDescription('Mark trip as delivered')
            ->addArgument('trip_id')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tripId = new Trip\Id(
            Uuid::fromString(
                $input->getArgument('trip_id'),
                $this->uuidValidator
            )
        );
        $this->tripCommandBus->handle(
            new MarkTripAsDeliveredCommand($tripId),
        );

        return self::SUCCESS;
    }
}