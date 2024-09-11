<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderMoved;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class FolderMovedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $ownerId = Id::createNew();
        $metadata = [1, 2, 3];
        $folderId = Id::createNew();
        $destinationId = Id::createNew();

        $folderMoved = FolderMoved::fromProperties(
            $ownerId,
            $metadata,
            $folderId,
            $destinationId
        );

        //Ensure that the getters return the expected values.
        Assert::assertEquals($destinationId, $folderMoved->destinationId());
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

        $destinationId = Id::createNew();

        $folderMoved = FolderMoved::fromProperties(
            $ownerId,
            $metadata,
            $folderCreated->aggregateId(),
            $destinationId
        );

        $folder = $folderCreated->createFolder();
        $folderPrime = $folderMoved->transformFolder($folder);

        //Ensure that the parent property of the new folder is set.
        Assert::assertEquals($destinationId, $folderPrime->parent());

        //Ensure that none of the other properties were changed.
        Assert::assertEquals($folder->ownerId(), $folderPrime->ownerId());
        Assert::assertEquals($folder->deleted(), $folderPrime->deleted());
        Assert::assertEquals($folder->name(), $folderPrime->name());
    }
}
