<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\LoggedFolderOpened\Event;

use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Event\LoggedFolderOpened;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class LoggedFolderOpenedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $authorizerId = Id::createNew();
        $metadata = [1, 2, 3];
        $folderId = Id::createNew();

        $folderOpened = LoggedFolderOpened::fromProperties(
            $authorizerId,
            $metadata,
            $folderId
        );

        Assert::assertEquals($authorizerId, $folderOpened->authorizerId());
        Assert::assertEquals($metadata, $folderOpened->eventMetadata());
        Assert::assertEquals($folderId, $folderOpened->folderId());
    }
}
