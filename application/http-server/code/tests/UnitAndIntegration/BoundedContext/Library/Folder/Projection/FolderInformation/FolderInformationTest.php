<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class FolderInformationTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testConstructionAndAccessors(): void
    {
        $folderId = Id::createNew();
        $folderName = 'folder';
        $folderParent = 'parent-id';
        $folderOwnerId = Id::createNew();

        $folderInfo = FolderInformation::fromProperties(
            $folderId->id(),
            $folderName,
            $folderParent,
            $folderOwnerId->id()
        );

        Assert::assertEquals($folderId->id(), $folderInfo->folderId());
        Assert::assertEquals($folderName, $folderInfo->name());
        Assert::assertEquals($folderParent, $folderInfo->parent());
        Assert::assertEquals($folderOwnerId->id(), $folderInfo->ownerId());
    }

    /**
     * @test
     */
    public function testUpdateName(): void
    {
        $folderId = Id::createNew();
        $folderName = 'folder';
        $folderParent = null;
        $folderOwnerId = Id::createNew();

        $folderInfo = FolderInformation::fromProperties(
            $folderId->id(),
            $folderName,
            $folderParent,
            $folderOwnerId->id()
        );

        $folderInfo->updateName('newName');

        Assert::assertEquals('newName', $folderInfo->name());
    }

    /**
     * @test
     */
    public function testUpdateParent(): void
    {
        $folderId = Id::createNew();
        $folderName = 'folder';
        $folderParent = 'old-parent';
        $folderOwnerId = Id::createNew();

        $folderInfo = FolderInformation::fromProperties(
            $folderId->id(),
            $folderName,
            $folderParent,
            $folderOwnerId->id()
        );

        $folderInfo->updateParent('new-parent');

        Assert::assertEquals('new-parent', $folderInfo->parent());
    }
}
