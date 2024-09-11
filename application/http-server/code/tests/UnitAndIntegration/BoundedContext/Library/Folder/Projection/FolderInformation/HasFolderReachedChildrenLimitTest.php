<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\HasFolderReachedChildrenLimit;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class HasFolderReachedChildrenLimitTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testHasFolderReachedChildrenLimit(): void
    {
        // turn off doctrine output, and make an option on the kernel to turn it on for a particular test
        $ownerId = Id::createNew()->id();
        $folder = $this->createFolderUnderFolder(null, $ownerId);
        $folderAndChildren = [
            $this->serializeFolderInformation($folder),
        ];
        for ($i = 0; $i < 65530; ++$i) {
            $folderAndChildren[] =
                $this->serializeFolderInformation(
                    $this->createFolderUnderFolder(
                        $folder->folderId(),
                        $ownerId
                    )
                );
        }

        $collection = $this->getProjectionDocumentManager()
            ->getDocumentCollection(FolderInformation::class);
        $collection->insertMany($folderAndChildren);

        $service = $this->getContainer()
            ->get(HasFolderReachedChildrenLimit::class);

        Assert::assertFalse(
            $service->hasFolderReachedChildrenLimit($folder->folderId())
        );

        $this->getProjectionDocumentManager()
            ->persist(
                $this->createFolderUnderFolder($folder->folderId(), $ownerId)
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertTrue(
            $service->hasFolderReachedChildrenLimit($folder->folderId())
        );
    }

    private function createFolderUnderFolder(
        ?string $createUnderFolderId,
        string $ownerId
    ): FolderInformation {
        return FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $createUnderFolderId,
            $ownerId
        );
    }

    private function serializeFolderInformation(FolderInformation $folderInformation): array
    {
        return [
            'id' => $folderInformation->folderId(),
            'name' => $folderInformation->name(),
            'parent' => $folderInformation->parent(),
            'ownerId' => $folderInformation->ownerId(),
        ];
    }
}
