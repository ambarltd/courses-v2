<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class FolderCreatedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $ownerId = Id::createNew();
        $parentFolderId = Id::createNew();
        $metadata = [1, 2, 3];

        $folderCreated = FolderCreated::fromProperties(
            $ownerId,
            $metadata,
            'folder',
            $parentFolderId
        );

        Assert::assertInstanceOf(
            Id::class,
            $folderCreated->eventId()
        );
        Assert::assertInstanceOf(
            Id::class,
            $folderCreated->aggregateId()
        );
        Assert::assertNotEquals(
            $folderCreated->eventId(),
            $folderCreated->aggregateId()
        );
        Assert::assertEquals(
            $ownerId,
            $folderCreated->authorizerId()
        );
        Assert::assertEquals(
            null,
            $folderCreated->sourceEventId()
        );

        //Ensure that getters return the expected values.
        Assert::assertEquals('folder', $folderCreated->name());
        Assert::assertEquals($parentFolderId, $folderCreated->parent());
        Assert::assertEquals($ownerId, $folderCreated->ownerId());
    }

    /**
     * @test
     */
    public function testCreateAggregate(): void
    {
        $ownerId = Id::createNew();
        $parentFolderId = Id::createNew();
        $metadata = [1, 2, 3];

        $folder = FolderCreated::fromProperties(
            $ownerId,
            $metadata,
            'folder',
            $parentFolderId
        )->createFolder();

        //Ensure that the aggregate contains the expected values.
        Assert::assertEquals('folder', $folder->name());
        Assert::assertEquals(false, $folder->deleted());
        Assert::assertEquals($ownerId, $folder->ownerId());
        Assert::assertEquals($parentFolderId, $folder->parent());
    }
}
