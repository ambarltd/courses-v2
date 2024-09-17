<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestConnections extends Command
{
    private DocumentManager $reactionDocumentManager;

    private DocumentManager $projectionDocumentManager;

    private SQLEventStoreConnection $sqlEventStoreConnection;

    public function __construct(
        DocumentManager $reactionDocumentManager,
        DocumentManager $projectionDocumentManager,
        SQLEventStoreConnection $sqlEventStoreConnection,
    ) {
        parent::__construct();

        $this->reactionDocumentManager = $reactionDocumentManager;
        $this->projectionDocumentManager = $projectionDocumentManager;
        $this->sqlEventStoreConnection = $sqlEventStoreConnection;
    }

    protected function configure(): void
    {
        try {
            $this->setName('galeas:dbs:connection_tests')
                ->setDescription('Test connections to databases and queues.');
        } catch (\Throwable $throwable) {
            return;
        }
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        try {
            // PROJECTION DB
            $this->projectionDocumentManager->getClient()->listDatabases();
            $output->writeln('Projection DB OK');

            // REACTION DB
            $this->reactionDocumentManager->getClient()->listDatabases();
            $output->writeln('Reaction DB OK');

            // EVENT STORE
            $eventStoreStatus = $this->sqlEventStoreConnection->getConnection()->isConnected() ||
                null !== $this->sqlEventStoreConnection->getConnection()->executeQuery("SELECT 1");

            if (true !== $eventStoreStatus) {
                throw new \RuntimeException('Cannot connect to event store');
            }
            $output->writeln('EventStore DB OK');

        } catch (\Throwable $throwable) {
            $output->writeln('Error, could not verify all connections.');
            $output->writeln($throwable->getMessage());

            return 1;
        }

        $output->writeln('All OK. Connections checked.');

        return 0;
    }
}
