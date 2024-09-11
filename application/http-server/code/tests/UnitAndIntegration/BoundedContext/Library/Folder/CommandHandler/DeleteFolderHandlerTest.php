<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\CommandHandler;

use Galeas\Api\BoundedContext\Library\Folder\Command\DeleteFolder;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\DeleteFolder\DeleteFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\DeleteFolder\DoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderDeleted;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class DeleteFolderHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $command = new DeleteFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            )
        );

        $handler->handle($command);

        /** @var FolderDeleted $storedEvent */
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
            $storedEvent->aggregateId()->id()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\DeleteFolder\FolderNotOwned
     */
    public function testFolderNotOwned(): void
    {
        $command = new DeleteFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                function (string $folderId, string $userId) use ($command): bool {
                    if (
                        $folderId === $command->folderId &&
                        $userId === $command->authorizerId
                    ) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $handler->handle($command);
    }
}
