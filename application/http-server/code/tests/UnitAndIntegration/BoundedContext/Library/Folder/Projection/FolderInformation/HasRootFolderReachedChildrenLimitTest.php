<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\MongoDBException;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\HasRootFolderReachedChildrenLimit;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class HasRootFolderReachedChildrenLimitTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws MongoDBException
     */
    public function testHasRootFolderReachedChildrenLimit(): void
    {
        $ownerId = Id::createNew()->id();
        $nonOwnerId = Id::createNew()->id();

        $allFolders = [];
        for ($i = 0; $i < 65530; ++$i) {
            $allFolders[] =
                $this->serializeFolderInformation(
                    $this->createFolderUnderFolder(
                        null,
                        $ownerId
                    )
                );
        }

        $collection = $this->getProjectionDocumentManager()
            ->getDocumentCollection(FolderInformation::class);
        $collection->insertMany($allFolders);

        $service = $this->getContainer()
            ->get(HasRootFolderReachedChildrenLimit::class);

        Assert::assertFalse(
            $service->hasRootFolderReachedChildrenLimit($ownerId)
        );
        Assert::assertFalse(
            $service->hasRootFolderReachedChildrenLimit($nonOwnerId)
        );

        $this->getProjectionDocumentManager()
            ->persist(
                $this->createFolderUnderFolder(null, $ownerId)
            );
        $this->getProjectionDocumentManager()->flush();

        Assert::assertTrue(
            $service->hasRootFolderReachedChildrenLimit($ownerId)
        );
        Assert::assertFalse(
            $service->hasRootFolderReachedChildrenLimit($nonOwnerId)
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
