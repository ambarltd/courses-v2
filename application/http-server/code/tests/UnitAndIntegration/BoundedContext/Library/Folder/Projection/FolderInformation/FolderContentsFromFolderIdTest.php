<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderContentsFromFolderId;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\ValidIds;

class FolderContentsFromFolderIdTest extends KernelTestBase
{
    /**
     * @test
     *
     * @expectedException \Galeas\Api\Common\ExceptionBase\ProjectionCannotRead
     */
    public function testGetFolderContentsForInvalidFolderId(): void
    {
        $folderContentsService = $this->getContainer()
            ->get(FolderContentsFromFolderId::class);

        $folderContentsService->folderContentsFromFolderId('invalid-id');
    }

    /**
     * @test
     *
     * @expectedException \Galeas\Api\Common\ExceptionBase\ProjectionCannotRead
     */
    public function testGetFolderContentsForNonexistentFolder(): void
    {
        $folderContentsService = $this->getContainer()
            ->get(FolderContentsFromFolderId::class);

        $folderContentsService->folderContentsFromFolderId(ValidIds::listValidIds()[0]);
    }

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
            'owner'
        );
        $a1 = FolderInformation::fromProperties(
            'idA1',
            'nameA1',
            'idA',
            'owner'
        );
        $a1a = FolderInformation::fromProperties(
            'idA1A',
            'nameA1A',
            'idA1',
            'owner'
        );
        $a1b = FolderInformation::fromProperties(
            'idA1B',
            'nameA1B',
            'idA1',
            'owner'
        );
        $a1a1 = FolderInformation::fromProperties(
            'idA1A1',
            'nameA1A1',
            'idA1A',
            'owner'
        );

        $this->getProjectionDocumentManager()->persist($a);
        $this->getProjectionDocumentManager()->persist($a1);
        $this->getProjectionDocumentManager()->persist($a1a);
        $this->getProjectionDocumentManager()->persist($a1b);
        $this->getProjectionDocumentManager()->persist($a1a1);
        $this->getProjectionDocumentManager()->flush();

        $folderContentsService = $this->getContainer()
            ->get(FolderContentsFromFolderId::class);

        $folderContents = $folderContentsService->folderContentsFromFolderId('idA1');

        Assert::assertEquals(
            [
                'folder' => [
                    'id' => 'idA1',
                    'name' => 'nameA1',
                    'parent' => 'idA',
                ],
                'childrenFolders' => [
                    [
                        'id' => 'idA1A',
                        'name' => 'nameA1A',
                    ],
                    [
                        'id' => 'idA1B',
                        'name' => 'nameA1B',
                    ],
                ],
            ],
            $folderContents
        );
    }
}
