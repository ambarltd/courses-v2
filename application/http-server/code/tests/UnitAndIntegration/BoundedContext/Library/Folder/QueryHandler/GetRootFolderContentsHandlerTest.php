<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\QueryHandler;

use Galeas\Api\BoundedContext\Library\Folder\Query\GetRootFolderContents;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetRootFolderContents\GetRootFolderContentsHandler;
use Galeas\Api\BoundedContext\Library\Folder\QueryHandler\GetRootFolderContents\RootFolderContentsFromOwnerId;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class GetRootFolderContentsHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testSuccess(): void
    {
        $authorizerId = Id::createNew();
        $metadata = $this->mockMetadata();

        $query = new GetRootFolderContents();
        $query->authorizerId = $authorizerId->id();
        $query->metadata = $metadata;

        $handler = new GetRootFolderContentsHandler(
            $this->mockForCommandHandlerWithCallback(
                RootFolderContentsFromOwnerId::class,
                'rootFolderContentsFromOwnerId',
                function (string $ownerId) use ($query): array {
                    if ($ownerId === $query->authorizerId) {
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
}
