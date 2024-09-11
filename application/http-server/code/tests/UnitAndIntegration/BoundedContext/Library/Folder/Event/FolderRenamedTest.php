<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderRenamed;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class FolderRenamedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $authorizerId = Id::createNew();
        $metadata = [1, 2, 3];
        $folderId = Id::createNew();

        $folderRenamed = FolderRenamed::fromProperties(
            $authorizerId,
            $metadata,
            $folderId,
            'newName'
        );

        //Ensure that the getters return the expected values.
        Assert::assertEquals('newName', $folderRenamed->name());
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

        $folderRenamed = FolderRenamed::fromProperties(
            $ownerId,
            $metadata,
            $folderCreated->aggregateId(),
            'newName'
        );

        $folder = $folderCreated->createFolder();
        $folderPrime = $folderRenamed->transformFolder($folder);

        //Ensure that the transformed folder contains the new name.
        Assert::assertEquals('newName', $folderPrime->name());

        //Ensure that the other properties were not modified.
        Assert::assertEquals($folder->ownerId(), $folderPrime->ownerId());
        Assert::assertEquals($folder->parent(), $folderPrime->parent());
        Assert::assertEquals($folder->deleted(), $folderPrime->deleted());
    }
}
