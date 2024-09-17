<?php

declare(strict_types=1);

namespace Galeas\Api\Console;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DBMigration extends Command
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
            $this->setName('galeas:dbs:updates')
                ->setDescription('Create Event Store');
        } catch (\Throwable $throwable) {
            return;
        }
    }
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int {
        $connection = $this->sqlEventStoreConnection->getConnection();
        $connection
            ->executeStatement("
                CREATE TABLE IF NOT EXISTS event (
                    id BIGSERIAL NOT NULL,
                    event_id VARCHAR(56) NOT NULL UNIQUE,
                    aggregate_id VARCHAR(56) NOT NULL,
                    aggregate_version BIGINT NOT NULL,
                    causation_id VARCHAR(56) NOT NULL,
                    correlation_id VARCHAR(56) NOT NULL,
                    recorded_on VARCHAR(30) NOT NULL,
                    event_name VARCHAR(255) NOT NULL,
                    json_payload TEXT NOT NULL,
                    json_metadata TEXT NOT NULL,
                    PRIMARY KEY (id)
                );");
        $this->executeIndexStatementAndIgnoreExceptions("CREATE UNIQUE INDEX idx_event_aggregate_id_version ON event(aggregate_id, aggregate_version);");
        $this->executeIndexStatementAndIgnoreExceptions("CREATE INDEX idx_event_causation_id ON event(causation_id);");
        $this->executeIndexStatementAndIgnoreExceptions("CREATE INDEX idx_event_correlation_id ON event(correlation_id);");
        $this->executeIndexStatementAndIgnoreExceptions("CREATE INDEX idx_occurred_on ON event(recorded_on);");
        $this->executeIndexStatementAndIgnoreExceptions("CREATE INDEX idx_event_name ON event(event_name);");
        $this->projectionDocumentManager->getSchemaManager()->createCollections();
        $this->projectionDocumentManager->getSchemaManager()->createSearchIndexes();
        $this->reactionDocumentManager->getSchemaManager()->createCollections();
        $this->reactionDocumentManager->getSchemaManager()->createSearchIndexes();

        return 0;
    }

    private function executeIndexStatementAndIgnoreExceptions(string $statement):  void
    {
        try {
            $this->sqlEventStoreConnection->getConnection()
                ->executeStatement($statement);
        } catch (\Exception $e) {
            return;
        }
    }
}
