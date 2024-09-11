<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\LoggedRootFolderOpened\CommandHandler;

use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Command\LogRootFolderOpened;
use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\CommandHandler\LogRootFolderOpened\LogRootFolderOpenedHandler;
use Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Event\LoggedRootFolderOpened;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class LogRootFolderOpenedHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $command = new LogRootFolderOpened();
        $command->authorizerId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new LogRootFolderOpenedHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);

        /** @var LoggedRootFolderOpened $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[0];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );

        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->authorizerId()->id()
        );
        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->ownerId()->id()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }
}
