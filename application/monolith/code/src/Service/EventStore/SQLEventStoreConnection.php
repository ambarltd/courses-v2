<?php

declare(strict_types=1);

namespace Galeas\Api\Service\EventStore;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\TransactionIsolationLevel;

class SQLEventStoreConnection
{
    private Connection $connection;

    /**
     * To prevent race conditions, whenever we read from an aggregate, we block any other reads,
     * until we have added new events to the aggregate (or the connection is closed).
     *
     * I.e. event sourcing with pessimistic locking inside an aggregate.
     *
     * @see https://dev.mysql.com/doc/refman/5.7/en/innodb-locking-reads.html
     * @see https://en.wikipedia.org/wiki/Isolation_(database_systems)#Isolation_levels
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(
        string $databaseName,
        string $databaseHost,
        string $databasePort,
        string $databaseUser,
        string $databasePassword
    ) {
        $configuration = new Configuration();
        // Force explicit transaction use. Setting autoCommit to false, will (perhaps unintuitively)
        // begin a transaction when creating a query without an active transaction.
        $configuration->setAutoCommit(true);

        try {
            $this->connection = DriverManager::getConnection(
                [
                    'dbname' => $databaseName,
                    'host' => $databaseHost,
                    'port' => $databasePort,
                    'user' => $databaseUser,
                    'password' => $databasePassword,
                    'driver' => 'pdo_pgsql',
                    'sslmode' => 'require'
                ],
                $configuration,
                null
            );
        } catch (DriverException $DBALException) {
            throw new \InvalidArgumentException(sprintf('Could not connect. dbname "%s" user "%s" password *** host "%s" port "%s" driver "%s"', $databaseName, $databaseUser, $databaseHost, $databasePort, 'pdo_pgsql'));
        }

        $this->connection->setTransactionIsolation(TransactionIsolationLevel::SERIALIZABLE);
    }

    public function getConnection(): Connection
    {
        return $this->connection;
    }
}
