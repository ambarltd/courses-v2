<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Kernel;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class KernelTestBase extends TestCase
{
    private ?Kernel $kernel;

    private ?Container $container;

    public function setUp(): void
    {
        if (null !== $this->kernel && null !== $this->container) {
            $this->kernel->boot();
            $this->container = $this->containerFromKernel($this->kernel);

            return;
        }

        parent::setUp();

        require __DIR__.'/../../vendor/autoload.php';

        // debug = true, because running a test should refresh the cache
        // this is only run once, so it shouldn't impact CI time
        $this->kernel = new Kernel("test", true);
        $this->kernel->boot();
        $this->container = $this->containerFromKernel($this->kernel);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->deleteDatabasesAndCloseConnections();
        $this->kernel->shutdown();

        $this->container->reset();
    }

    protected function kernelHandleRequest(Request $request): Response
    {
        return $this->kernel->handle($request);
    }

    protected function getContainer(): Container
    {
        return $this->container;
    }

    protected function getProjectionDocumentManager(): DocumentManager
    {
        return $this->container->get('doctrine_mongodb.odm.projection_document_manager');
    }

    protected function getReactionDocumentManager(): DocumentManager
    {
        return $this->container->get('doctrine_mongodb.odm.reaction_document_manager');
    }

    protected function getEventStoreConnection(): Connection
    {
        return $this->container->get(SQLEventStoreConnection::class)->getConnection();
    }

    private function containerFromKernel(Kernel $kernel): Container
    {
        $container = $kernel->getContainer();
        if ($container instanceof Container) {
            return $container;
        }

        throw new \RuntimeException();
    }

    private function deleteDatabasesAndCloseConnections(): void
    {
        $projectionDocumentManager = self::getProjectionDocumentManager();
        $projectionDocumentManager->clear();
        $projectionDatabase = $projectionDocumentManager->getClient()
            ->selectDatabase(
                $this->container->getParameter('mongodb_projection_database_name')
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
                $this->container->getParameter('mongodb_reaction_database_name')
            );
        foreach ($reactionDatabase->listCollections() as $collection) {
            $reactionDatabase->selectCollection(
                $collection->getName()
            )->deleteMany([]);
        }

        $eventStoreConnection = $this->getEventStoreConnection(); // connects to the test database
        $eventStoreConnection->beginTransaction();
        $eventStoreConnection->executeStatement('TRUNCATE TABLE event');
        $eventStoreConnection->commit();
        $eventStoreConnection->close();
    }
}