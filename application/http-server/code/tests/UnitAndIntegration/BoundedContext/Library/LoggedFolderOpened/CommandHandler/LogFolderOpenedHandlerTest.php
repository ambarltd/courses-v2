<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\LoggedFolderOpened\CommandHandler;

use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Command\LogFolderOpened;
use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\CommandHandler\LogFolderOpened\DoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\CommandHandler\LogFolderOpened\LogFolderOpenedHandler;
use Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Event\LoggedFolderOpened;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class LogFolderOpenedHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $command = new LogFolderOpened();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new LogFolderOpenedHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            )
        );

        $handler->handle($command);

        /** @var LoggedFolderOpened $storedEvent */
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
            $command->folderId,
            $storedEvent->folderId()->id()
        );
        Assert::assertNotEquals(
            $storedEvent->aggregateId()->id(),
            $storedEvent->folderId()->id()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\LoggedFolderOpened\CommandHandler\LogFolderOpened\FolderNotOwned
     */
    public function testFolderNotOwned(): void
    {
        $command = new LogFolderOpened();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new LogFolderOpenedHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                function (string $folderId) use ($command): bool {
                    if ($folderId === $command->folderId) {
                        return false;
                    }

                    return true;
                }
            )
        );
        $handler->handle($command);
    }
}
