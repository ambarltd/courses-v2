<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Aggregate;

use Galeas\Api\BoundedContext\Library\Folder\Aggregate\Folder;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class FolderTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $folderId = Id::createNew();
        $folderName = 'test-name';
        $folderParent = null;
        $folderOwnerId = Id::createNew();
        $folderDeleted = false;

        $folder = Folder::fromProperties(
            $folderId,
            $folderName,
            $folderParent,
            $folderOwnerId,
            $folderDeleted
        );

        Assert::assertEquals($folderId, $folder->id());
        Assert::assertEquals($folderName, $folder->name());
        Assert::assertEquals($folderParent, $folder->parent());
        Assert::assertEquals($folderOwnerId, $folder->ownerId());
        Assert::assertEquals($folderDeleted, $folder->deleted());
    }
}
