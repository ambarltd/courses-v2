<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\QueryHandler;

use Galeas\Api\BoundedContext\Library\Folder\Query\GetFolderContents;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents\DoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents\FolderContentsFromFolderId;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents\GetFolderContentsHandler;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class GetFolderContentsHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testSuccess(): void
    {
        $authorizerId = Id::createNew();
        $folderId = Id::createNew();
        $metadata = $this->mockMetadata();

        $query = new GetFolderContents();
        $query->authorizerId = $authorizerId->id();
        $query->folderId = $folderId->id();
        $query->metadata = $metadata;

        $handler = new GetFolderContentsHandler(
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            ),
            $this->mockForCommandHandlerWithCallback(
                FolderContentsFromFolderId::class,
                'folderContentsFromFolderId',
                function (string $folderId) use ($query): array {
                    if ($folderId === $query->folderId) {
                        return ['expected'];
                    }

                    return ['unexpected'];
                }
            )
        );

        Assert::assertEquals(
            ['expected'],
            $handler->handle($query)
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetFolderContents\FolderNotOwned
     */
    public function testFolderNotOwned(): void
    {
        $authorizerId = Id::createNew();
        $folderId = Id::createNew();
        $metadata = $this->mockMetadata();

        $query = new GetFolderContents();
        $query->authorizerId = $authorizerId->id();
        $query->folderId = $folderId->id();
        $query->metadata = $metadata;

        $handler = new GetFolderContentsHandler(
            $this->mockForCommandHandlerWithCallback(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                function (string $folderId) use ($query): bool {
                    if ($folderId === $query->folderId) {
                        return false;
                    }

                    return true;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                FolderContentsFromFolderId::class,
                'folderContentsFromFolderId',
                ['expected']
            )
        );
        $handler->handle($query);
    }
}
