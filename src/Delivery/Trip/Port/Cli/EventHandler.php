<?php

declare(strict_types=1);

namespace App\Delivery\Trip\Port\Cli;

use App\Delivery\Shared\EventStoreRepository;
use App\Shared\Type\EventDispatcher;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class EventHandler extends Command
{
    public function __construct(
        private readonly EventDispatcher $eventDispatcher,
        private readonly EventStoreRepository $eventStoreRepository,
        string $name = null,
    ) {
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setName('delivery:trip:event:handler')
            ->addArgument(
                name: 'batchSize',
                mode: InputArgument::OPTIONAL,
                description: 'batch size number',
                default: 1000,
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $batchSize = (int) $input->getArgument('batchSize');
        do {
            $count = 0;
            $eventIdsToDelete = [];
            $domainEventEntities = $this->eventStoreRepository->getOldestEvents($batchSize);
            foreach ($domainEventEntities as $domainEventEntity) {
                $count++;
                $this->eventDispatcher->dispatch($domainEventEntity->getDomainEvent());
                $eventIdsToDelete[] = $domainEventEntity->getId();
            }
            $this->eventStoreRepository->delete($eventIdsToDelete);
        } while ($count > 0);

        return self::SUCCESS;
    }
}