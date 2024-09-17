<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Doctrine\DBAL\Connection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\Kernel;
use Galeas\Api\Service\EventStore\SQLEventStoreConnection;
use Galeas\Api\Service\ODM\DocumentManagerForTests;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class KernelTestBase extends TestCase
{
    /**
     * @var Kernel
     */
    private $kernel;

    /**
     * @var Container
     */
    private $container;

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
        $this->kernel = new Kernel("test", false);
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
        /** @var DocumentManagerForTests $documentManagerForTests */
        $documentManagerForTests = $this->container->get(DocumentManagerForTests::class);
        return $documentManagerForTests->projectionDocumentManager();
    }

    protected function getReactionDocumentManager(): DocumentManager
    {
        /** @var DocumentManagerForTests $documentManagerForTests */
        $documentManagerForTests = $this->container->get(DocumentManagerForTests::class);
        return $documentManagerForTests->reactionDocumentManager();
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
        $projectionDocumentManager = $this->getProjectionDocumentManager();
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

        $reactionDocumentManager = $this->getReactionDocumentManager();
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

        $eventStoreConnection = $this->getEventStoreConnection();
        $eventStoreConnection->beginTransaction();
        $eventStoreConnection->executeStatement('TRUNCATE TABLE event');
        $eventStoreConnection->commit();
        $eventStoreConnection->close();
    }
}