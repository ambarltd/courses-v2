<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration;

use Doctrine\ODM\MongoDB\DocumentManager;

abstract class ProjectionAndReactionIntegrationTest extends IntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->deleteProjectionAndReactionDatabases();
    }

    protected function tearDown(): void
    {
        $this->deleteProjectionAndReactionDatabases();
        parent::tearDown();
    }

    protected function getProjectionDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb.odm.projection_document_manager');
    }

    protected function getReactionDocumentManager(): DocumentManager
    {
        return $this->getContainer()->get('doctrine_mongodb.odm.reaction_document_manager');
    }

    private function deleteProjectionAndReactionDatabases(): void
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

        $reactionDocumentManager = $this->getReactionDocumentManager();
        $reactionDocumentManager->clear();
        $reactionDatabase = $reactionDocumentManager->getClient()
            ->selectDatabase(
                $this->getContainer()->getParameter('mongodb_reaction_database_name')
            )
        ;
        foreach ($reactionDatabase->listCollections() as $collection) {
            $reactionDatabase->selectCollection(
                $collection->getName()
            )->deleteMany([]);
        }
    }
}
