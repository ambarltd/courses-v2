<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderDeleted;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class FolderDeletedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $authorizerId = Id::createNew();
        $folderId = Id::createNew();
        $metadata = [1, 2, 3];

        $folderDeleted = FolderDeleted::fromProperties(
            $authorizerId,
            $metadata,
            $folderId
        );

        //This has no properties of its own, so no getters to test directly.
        Assert::assertTrue(true);
    }

    /**
     * @test
     */
    public function testTransformAggregate(): void
    {
        $ownerId = Id::createNew();
        $metadata = [1, 2, 3];

        $folderCreated = FolderCreated::fromProperties(
            $ownerId,
            $metadata,
            'folder',
            null
        );

        $folderDeleted = FolderDeleted::fromProperties(
            $ownerId,
            $metadata,
            $folderCreated->aggregateId()
        );

        $folder = $folderCreated->createFolder();
        $folderPrime = $folderDeleted->transformFolder($folder);

        //Ensure that the aggregate's deleted property is properly set.
        Assert::assertEquals(true, $folderPrime->deleted());

        //And that none of the other properties have been modified.
        Assert::assertEquals($folder->parent(), $folderPrime->parent());
        Assert::assertEquals($folder->ownerId(), $folderPrime->ownerId());
        Assert::assertEquals($folder->name(), $folderPrime->name());
    }
}
