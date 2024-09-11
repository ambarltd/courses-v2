<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\DoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class DoesFolderExistAndIsItOwnedByUserTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testGetFolderContentsFromFolderId(): void
    {
        $a = FolderInformation::fromProperties(
            'idA',
            'nameA',
            null,
            'ownerId1'
        );
        $b = FolderInformation::fromProperties(
            'idB',
            'nameB',
            null,
            'ownerId2'
        );

        $this->getProjectionDocumentManager()->persist($a);
        $this->getProjectionDocumentManager()->persist($b);
        $this->getProjectionDocumentManager()->flush();

        $service = $this->getContainer()
            ->get(DoesFolderExistAndIsItOwnedByUser::class);

        Assert::assertTrue(
            $service->doesFolderExistAndIsItOwnedByUser('idA', 'ownerId1')
        );
        Assert::assertFalse(
            $service->doesFolderExistAndIsItOwnedByUser('idA', 'ownerId2')
        );
        Assert::assertTrue(
            $service->doesFolderExistAndIsItOwnedByUser('idB', 'ownerId2')
        );
        Assert::assertFalse(
            $service->doesFolderExistAndIsItOwnedByUser('idB', 'ownerId1')
        );

        Assert::assertFalse(
            $service->doesFolderExistAndIsItOwnedByUser('ida', 'ownerId1')
        );
        Assert::assertFalse(
            $service->doesFolderExistAndIsItOwnedByUser('bla_bla_bla', 'ownerId1')
        );
        Assert::assertFalse(
            $service->doesFolderExistAndIsItOwnedByUser('idA', 'bla_bla_bla')
        );
    }
}
