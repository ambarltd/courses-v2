<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Kernel;
use Galeas\Api\Service\DBMigration\DBMigration;
use Galeas\Api\Service\EventStore\SQLEventStore;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;

abstract class ResetsEventStoreAndProjectionsIntegrationTest extends IntegrationTest
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        require __DIR__.'/../../vendor/autoload.php';

        // debug = true, because running a test should refresh the cache
        // this is only run once, so it shouldn't impact CI time
        $kernel = new Kernel('test', false);
        $kernel->boot();
        $container = $kernel->getContainer();
        $container->get(DBMigration::class)->createEventStoreAndHydrateProjections(false);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->truncateEventStoreAndProjectionDatabases();
    }

    protected function tearDown(): void
    {
        $this->truncateEventStoreAndProjectionDatabases();
        parent::tearDown();
    }

    protected function getSQLEventStore(): SQLEventStore
    {
        return $this->getContainer()->get(SQLEventStore::class);
    }

    protected function getProjectionDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb.odm.projection_document_manager');
    }

    private function truncateEventStoreAndProjectionDatabases(): void
    {
        $projectionDocumentManager = $this->getProjectionDocumentManager();
        $projectionDocumentManager->clear();
        $projectionDatabase = $projectionDocumentManager->getClient()
            ->selectDatabase(
                $this->getContainer()->getParameter('mongodb_projection_database_name')
            )
        ;
        foreach ($projectionDatabase->listCollections() as $collection) {
            $projectionDatabase->selectCollection(
                $collection->getName()
            )->deleteMany([]);
        }

        /** @var SQLEventStoreConnection $connection */
        $connection = $this->getContainer()->get(SQLEventStoreConnection::class);
        $connection = $connection->getConnection();
        $connection->executeStatement('TRUNCATE TABLE '.$this->getContainer()->getParameter('event_store_create_event_table_with_name').';');
    }
}
