<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\IsFolderAncestorOfDestinationFolder;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class IsFolderAncestorOfDestinationFolderTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testIsFolder(): void
    {
        $owner = Id::createNew()->id();
        //         _null_
        //        /      \
        //       a        b
        //       |        |
        //       a1       b1
        //      /  \
        //    a1a  a1b
        $a = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            null,
            $owner
        );
        $b = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            null,
            $owner
        );
        $a1 = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a->folderId(),
            $owner
        );
        $b1 = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $b->folderId(),
            $owner
        );
        $a1a = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1->folderId(),
            $owner
        );
        $a1b = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1->folderId(),
            $owner
        );

        $documentManager = $this->getProjectionDocumentManager();

        $documentManager->persist($a);
        $documentManager->persist($b);
        $documentManager->persist($a1);
        $documentManager->persist($b1);
        $documentManager->persist($a1a);
        $documentManager->persist($a1b);
        $documentManager->flush();

        $service = $this->getContainer()
            ->get(IsFolderAncestorOfDestinationFolder::class);

        Assert::assertTrue(
            $service->isFolderAncestorOfDestinationFolder($a->folderId(), $a1->folderId())
        );
        Assert::assertTrue(
            $service->isFolderAncestorOfDestinationFolder($a->folderId(), $a1a->folderId())
        );
        Assert::assertTrue(
            $service->isFolderAncestorOfDestinationFolder($a->folderId(), $a1b->folderId())
        );
        Assert::assertTrue(
            $service->isFolderAncestorOfDestinationFolder($a1->folderId(), $a1a->folderId())
        );
        Assert::assertTrue(
            $service->isFolderAncestorOfDestinationFolder($a1->folderId(), $a1b->folderId())
        );
        Assert::assertTrue(
            $service->isFolderAncestorOfDestinationFolder($b->folderId(), $b1->folderId())
        );

        Assert::assertFalse(
            $service->isFolderAncestorOfDestinationFolder($a->folderId(), $b->folderId())
        );
        Assert::assertFalse(
            $service->isFolderAncestorOfDestinationFolder($a->folderId(), $b1->folderId())
        );
        Assert::assertFalse(
            $service->isFolderAncestorOfDestinationFolder($b->folderId(), $a->folderId())
        );
        Assert::assertFalse(
            $service->isFolderAncestorOfDestinationFolder($b->folderId(), $a1->folderId())
        );
        Assert::assertFalse(
            $service->isFolderAncestorOfDestinationFolder($b->folderId(), $a1a->folderId())
        );
        Assert::assertFalse(
            $service->isFolderAncestorOfDestinationFolder($b->folderId(), $a1b->folderId())
        );
    }
}
