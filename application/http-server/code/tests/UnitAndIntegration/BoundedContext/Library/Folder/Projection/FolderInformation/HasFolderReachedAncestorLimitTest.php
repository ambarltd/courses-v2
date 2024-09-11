<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\HasFolderReachedAncestorLimit;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class HasFolderReachedAncestorLimitTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testHasFolderReachedAncestorLimit(): void
    {
        $ownerId = Id::createNew()->id();
        $topFolder = $this->createFolderUnderFolder(null, $ownerId);
        $deepestFolder = $topFolder;
        // deepest folder will now have 60 ancestors
        for ($i = 0; $i < 60; ++$i) {
            $deepestFolder = $this->createFolderUnderFolder($deepestFolder->folderId(), $ownerId);
        }
        $this->getProjectionDocumentManager()->flush();

        $service = $this->getContainer()
            ->get(HasFolderReachedAncestorLimit::class);

        Assert::assertFalse(
            $service->hasFolderReachedAncestorLimit($deepestFolder->folderId())
        );

        // deepest folder will now have 61 ancestors
        $deepestFolder = $this->createFolderUnderFolder($deepestFolder->folderId(), $ownerId);
        $this->getProjectionDocumentManager()->flush();
        Assert::assertTrue(
            $service->hasFolderReachedAncestorLimit($deepestFolder->folderId())
        );
    }

    private function createFolderUnderFolder(?string $createUnderFolderId, string $ownerId): FolderInformation
    {
        $createdFolder = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $createUnderFolderId,
            $ownerId
        );
        $this->getProjectionDocumentManager()->persist($createdFolder);

        return $createdFolder;
    }
}
