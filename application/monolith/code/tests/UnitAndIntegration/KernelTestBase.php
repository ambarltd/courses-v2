<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\DBALException;
use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Kernel;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use MongoDB\Exception\RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;
use Symfony\Component\DependencyInjection\Container;

/**
 * Integration tests. Which make use of the kernel to check how system components interact.
 *
 * POSSIBLE TODO - Delete test_topic_name topics from Kafka before / after each test.
 * POSSIBLE TODO - Allow manual retrieval of payloads out of test_topic_name topics.
 *
 * Do this if KernelTestBase tests are going to work as integration tests for command handlers.
 * It might be better to skip this, make command handlers non-kernel based, and do end to end tests
 * to test full functionality (which may or may not also require deleting of databases / queues).
 */
abstract class KernelTestBase extends TestCase
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var TestContainer
     */
    private $testContainer;

    /**
     * @var Container
     */
    private $nonTestContainer;

    /**
     * Recreating the database before and after every test is expensive.
     * It's cheaper to delete all documents from all collections.
     *
     * @throws DBALException|ConnectionException
     */
    private function deleteDatabasesAndCloseConnections(): void
    {
        $projectionDocumentManager = self::getProjectionDocumentManager();
        $projectionDocumentManager->clear();
        $projectionDatabase = $projectionDocumentManager->getClient()
            ->selectDatabase(
                $this->testContainer->getParameter('mongodb_projection_database_name')
            );
        foreach ($projectionDatabase->listCollections() as $collection) {
            $projectionDatabase->selectCollection(
                $collection->getName()
            )->deleteMany([]);
        }

        $reactionDocumentManager = self::getReactionDocumentManager();
        $reactionDocumentManager->clear();
        $reactionDatabase = $reactionDocumentManager->getClient()
            ->selectDatabase(
                $this->testContainer->getParameter('mongodb_reaction_database_name')
            );
        foreach ($reactionDatabase->listCollections() as $collection) {
            $reactionDatabase->selectCollection(
                $collection->getName()
            )->deleteMany([]);
        }

        $eventStoreConnection = $this->getEventStoreConnection(); // connects to the test database
        $eventStoreConnection->beginTransaction();
        $eventStoreConnection->exec('TRUNCATE TABLE event');
        $eventStoreConnection->commit();
        $eventStoreConnection->close();
    }

    protected function getContainer(): TestContainer
    {
        return $this->testContainer;
    }

    protected function getProjectionDocumentManager(): DocumentManager
    {
        return $this->testContainer->get('doctrine_mongodb.odm.projection_document_manager');
    }

    protected function getReactionDocumentManager(): DocumentManager
    {
        return $this->testContainer->get('doctrine_mongodb.odm.reaction_document_manager');
    }

    protected function getEventStoreConnection(): Connection
    {
        return $this->testContainer->get(SQLEventStoreConnection::class)->getConnection();
    }

    /**
     * @throws DBALException|ConnectionException|RuntimeException
     * @throws \RuntimeException
     */
    public function setUp(): void
    {
        if (null !== $this->kernel) {
            $this->kernel->boot();
            $this->nonTestContainer = $this->containerFromKernel($this->kernel);
            $this->testContainer = $this->nonTestContainer->get('test.service_container');

            return;
        }

        parent::setUp();

        require __DIR__.'/../../vendor/autoload.php';

        // debug = true, because running a test should refresh the cache
        // this is only run once, so it shouldn't impact CI time
        $this->kernel = new Kernel("production", true);
        $this->kernel->boot();
        $this->nonTestContainer = $this->containerFromKernel($this->kernel);
        $this->testContainer = $this->nonTestContainer->get('test.service_container');

        $this->deleteDatabasesAndCloseConnections();
    }

    /**
     * @throws \RuntimeException
     */
    private function containerFromKernel(Kernel $kernel): Container
    {
        $container = $kernel->getContainer();
        if ($container instanceof Container) {
            return $container;
        }

        throw new \RuntimeException();
    }

    /**
     * @throws DBALException|ConnectionException
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->deleteDatabasesAndCloseConnections();

        $this->kernel->shutdown();

        $this->testContainer->reset();
        $this->nonTestContainer->reset();
    }
}
