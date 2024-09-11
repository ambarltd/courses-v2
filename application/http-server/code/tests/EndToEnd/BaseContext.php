<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\EndToEnd;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Kernel;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\EventStore\SQLEventStore;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Symfony\Bundle\FrameworkBundle\Test\TestContainer;

abstract class BaseContext implements Context, SnippetAcceptingContext
{
    /**
     * @var Kernel
     */
    private static $kernel;

    /**
     * @var TestContainer
     */
    private static $container;

    /**
     * Behat does not expect input and output into every line.
     * Instead of having a lot of arbitrary fields in this class, and thus its objects,
     * $nextInput servers as a way of getting input from the last statement, and
     * passing output to the input of the next statement.
     *
     * @var mixed
     */
    private static $nextInput;

    public function __construct()
    {
    }

    /**
     * @return mixed
     */
    protected function getNextInput()
    {
        return self::$nextInput;
    }

    /**
     * @param mixed $nextInput
     */
    protected function setNextInput($nextInput): void
    {
        self::$nextInput = $nextInput;
    }

    /**
     * @throws \RuntimeException
     */
    private static function initialize(): void
    {
        if (null !== self::$kernel) {
            return;
        }

        require __DIR__.'/../../vendor/autoload.php';

        $environment = getenv('API_ENVIRONMENT_TYPE');
        if (!is_string($environment)) {
            throw new \RuntimeException();
        }
        // debug = true, because running a test should refresh the cache
        // this is only run once, so it shouldn't impact CI time
        self::$kernel = new Kernel($environment, true);
        self::$kernel->boot();

        self::$container = self::$kernel->getContainer()->get('test.service_container');
    }

    /**
     * Recreating the database before and after every test is expensive.
     * It's cheaper to delete all documents from all collections.
     *
     * @BeforeFeature
     * @AfterFeature
     * @BeforeScenario
     * @AfterScenario
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     * @throws \RuntimeException
     */
    public static function deleteDatabasesAndClearNextInput(): void
    {
        self::initialize();

        $environment = getenv('API_ENVIRONMENT_TYPE');

        if ('environment_test' !== $environment) {
            throw new \RuntimeException('Cannot delete databases unless in test mode');
        }

        $projectionDocumentManager = self::getProjectionDocumentManager();
        $projectionDocumentManager->clear();
        $projectionDatabase = $projectionDocumentManager->getClient()
            ->selectDatabase(
                self::$container->getParameter('mongodb_projection_database_name')
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
                self::$container->getParameter('mongodb_reaction_database_name')
            );
        foreach ($reactionDatabase->listCollections() as $collection) {
            $reactionDatabase->selectCollection(
                $collection->getName()
            )->deleteMany([]);
        }

        $eventStoreConnection = self::getEventStoreConnection(); // connects to the test database
        $eventStoreConnection->beginTransaction();
        $eventStoreConnection->exec('TRUNCATE TABLE event');
        $eventStoreConnection->commit();

        self::$nextInput = null;
    }

    /**
     * @throws \RuntimeException
     */
    protected static function getProjectionDocumentManager(): DocumentManager
    {
        self::initialize();

        return self::$container->get('doctrine_mongodb.odm.projection_document_manager');
    }

    /**
     * @throws \RuntimeException
     */
    protected static function getReactionDocumentManager(): DocumentManager
    {
        self::initialize();

        return self::$container->get('doctrine_mongodb.odm.reaction_document_manager');
    }

    /**
     * @throws \RuntimeException
     */
    private static function getEventStoreConnection(): Connection
    {
        self::initialize();

        return self::$container->get(SQLEventStoreConnection::class)->getConnection();
    }

    /**
     * @throws \RuntimeException
     */
    protected static function getEventStore(): EventStore
    {
        self::initialize();

        return self::$container->get(SQLEventStore::class);
    }
}
