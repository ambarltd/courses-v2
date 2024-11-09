<?php

declare(strict_types=1);

namespace Galeas\Api\Service\DBMigration;

use Doctrine\DBAL\Exception;
use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Galeas\Api\Service\Logger\PhpOutLogger;

class DBMigration
{
    private PhpOutLogger $phpOutLogger;

    private DocumentManager $projectionDocumentManager;

    private SQLEventStoreConnection $sqlEventStoreConnection;

    private string $eventStoreDatabaseName;
    private string $eventStoreTableName;
    private string $eventStoreCreateReplicationUserWithUsername;
    private string $eventStoreCreateReplicationUserWithPassword;
    private string $eventStoreCreateReplicationPublication;

    public function __construct(
        DocumentManager $projectionDocumentManager,
        SQLEventStoreConnection $sqlEventStoreConnection,
        PhpOutLogger $phpOutLogger,
        string $eventStoreDatabaseName,
        string $eventStoreTableName,
        string $eventStoreCreateReplicationUserWithUsername,
        string $eventStoreCreateReplicationUserWithPassword,
        string $eventStoreCreateReplicationPublication,
    ) {
        $this->projectionDocumentManager = $projectionDocumentManager;
        $this->sqlEventStoreConnection = $sqlEventStoreConnection;
        $this->phpOutLogger = $phpOutLogger;
        $this->eventStoreDatabaseName = $eventStoreDatabaseName;
        $this->eventStoreTableName = $eventStoreTableName;
        $this->eventStoreCreateReplicationUserWithUsername = $eventStoreCreateReplicationUserWithUsername;
        $this->eventStoreCreateReplicationUserWithPassword = $eventStoreCreateReplicationUserWithPassword;
        $this->eventStoreCreateReplicationPublication = $eventStoreCreateReplicationPublication;
    }

    /**
     * @throws Exception
     */
    public function createEventStoreAndHydrateProjections(bool $logErrors): void
    {
        try {
            $connection = $this->sqlEventStoreConnection->getConnection();
            $connection
                ->executeStatement(\sprintf('
                CREATE TABLE IF NOT EXISTS %s (
                    id BIGSERIAL NOT NULL,
                    event_id TEXT NOT NULL UNIQUE,
                    aggregate_id TEXT NOT NULL,
                    aggregate_version BIGINT NOT NULL,
                    causation_id TEXT NOT NULL,
                    correlation_id TEXT NOT NULL,
                    recorded_on TEXT NOT NULL,
                    event_name TEXT NOT NULL,
                    json_payload TEXT NOT NULL,
                    json_metadata TEXT NOT NULL,
                    PRIMARY KEY (id)
                );', $this->eventStoreTableName))
            ;

            // The following statements are created while ignoring exceptions because one might fail, and we still want to
            // the others. AND we don't know if we are executing this script for the first time (it gets executed whenever
            // we redeploy).
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf("CREATE USER %s REPLICATION LOGIN PASSWORD '%s';", $this->eventStoreCreateReplicationUserWithUsername, $this->eventStoreCreateReplicationUserWithPassword));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('GRANT CONNECT ON DATABASE"%s"TO %s;', $this->eventStoreDatabaseName, $this->eventStoreCreateReplicationUserWithUsername));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('GRANT USAGE ON SCHEMA public TO %s;', $this->eventStoreCreateReplicationUserWithUsername));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('GRANT SELECT ON TABLE %s TO %s;', $this->eventStoreTableName, $this->eventStoreCreateReplicationUserWithUsername));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('CREATE PUBLICATION %s FOR TABLE %s;', $this->eventStoreCreateReplicationPublication, $this->eventStoreTableName));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('CREATE UNIQUE INDEX event_store_idx_event_aggregate_id_version ON %s(aggregate_id, aggregate_version);', $this->eventStoreTableName));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('CREATE INDEX event_store_idx_event_causation_id ON %s(causation_id);', $this->eventStoreTableName));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('CREATE INDEX event_store_idx_event_correlation_id ON %s(correlation_id);', $this->eventStoreTableName));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('CREATE INDEX event_store_idx_occurred_on ON %s(recorded_on);', $this->eventStoreTableName));
            $this->executeStatementAndIgnoreExceptions($logErrors, \sprintf('CREATE INDEX event_store_idx_event_name ON %s(event_name);', $this->eventStoreTableName));
            $this->projectionDocumentManager->getSchemaManager()->createCollections();
            $this->projectionDocumentManager->getSchemaManager()->createSearchIndexes();
        } catch (\Throwable $throwable) {
            if ($logErrors) {
                throw $throwable;
            }

            return;
        }
    }

    private function executeStatementAndIgnoreExceptions(bool $logErrors, string $statement): void
    {
        try {
            $this->sqlEventStoreConnection->getConnection()
                ->executeStatement($statement)
            ;
        } catch (\Throwable $e) {
            if ($logErrors) {
                $this->phpOutLogger->warning($e::class);
                $this->phpOutLogger->warning($e->getMessage());
                $this->phpOutLogger->warning($e->getTraceAsString());
            }

            return;
        }
    }
}
