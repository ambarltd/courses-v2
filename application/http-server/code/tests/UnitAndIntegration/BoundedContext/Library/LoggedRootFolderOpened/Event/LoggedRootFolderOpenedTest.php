<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\LoggedRootFolderOpened\Event;

use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Event\LoggedRootFolderOpened;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class LoggedRootFolderOpenedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $authorizerId = Id::createNew();
        $metadata = [1, 2, 3];

        $loggedRootFolderOpened = LoggedRootFolderOpened::fromProperties(
            $authorizerId,
            $metadata
        );

        Assert::assertEquals($authorizerId, $loggedRootFolderOpened->authorizerId());
        Assert::assertEquals($metadata, $loggedRootFolderOpened->eventMetadata());
        Assert::assertEquals($authorizerId, $loggedRootFolderOpened->ownerId());
    }
}
