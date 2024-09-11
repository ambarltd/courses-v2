<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\CommandHandler;

use Galeas\Api\BoundedContext\Library\Folder\Command\MoveFolder;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\DoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\HasFolderReachedAncestorLimit;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\HasFolderReachedChildrenLimit;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\HasRootFolderReachedChildrenLimit;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\IsFolderAncestorOfDestinationFolder;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\MoveFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderMoved;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class MoveFolderHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $command = new MoveFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->destinationFolderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new MoveFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasRootFolderReachedChildrenLimit::class,
                'hasRootFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedAncestorLimit::class,
                'hasFolderReachedAncestorLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsFolderAncestorOfDestinationFolder::class,
                'isFolderAncestorOfDestinationFolder',
                false
            )
        );

        $handler->handle($command);

        /** @var FolderMoved $storedEvent */
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
            $command->destinationFolderId,
            null !== $storedEvent->destinationId() ? $storedEvent->destinationId()->id() : null
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\FolderNotOwned
     */
    public function testFolderNotOwned(): void
    {
        $command = new MoveFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->destinationFolderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new MoveFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                function (string $folderId, string $userId) use ($command) {
                    if (
                        $folderId === $command->folderId &&
                        $userId === $command->authorizerId
                    ) {
                        return false;
                    }

                    return true;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasRootFolderReachedChildrenLimit::class,
                'hasRootFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedAncestorLimit::class,
                'hasFolderReachedAncestorLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsFolderAncestorOfDestinationFolder::class,
                'isFolderAncestorOfDestinationFolder',
                false
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\RootFolderHasTooManyChildren
     */
    public function testRootFolderHasTooManyChildren(): void
    {
        $command = new MoveFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->destinationFolderId = null;
        $command->metadata = $this->mockMetadata();

        $handler = new MoveFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            ),
            $this->mockForCommandHandlerWithCallback(
                HasRootFolderReachedChildrenLimit::class,
                'hasRootFolderReachedChildrenLimit',
                function (string $userId) use ($command): bool {
                    if ($userId === $command->authorizerId) {
                        return true;
                    }

                    return false;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedAncestorLimit::class,
                'hasFolderReachedAncestorLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsFolderAncestorOfDestinationFolder::class,
                'isFolderAncestorOfDestinationFolder',
                false
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\DestinationFolderNotOwned
     */
    public function testDestinationFolderNotOwned(): void
    {
        $command = new MoveFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->destinationFolderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new MoveFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                function (string $folderId, string $userId) use ($command) {
                    if (
                        $folderId === $command->destinationFolderId &&
                        $userId === $command->authorizerId
                    ) {
                        return false;
                    }

                    return true;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasRootFolderReachedChildrenLimit::class,
                'hasRootFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedAncestorLimit::class,
                'hasFolderReachedAncestorLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsFolderAncestorOfDestinationFolder::class,
                'isFolderAncestorOfDestinationFolder',
                false
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\DestinationFolderHasTooManyAncestors
     */
    public function testDestinationFolderHasTooManyAncestors(): void
    {
        $command = new MoveFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->destinationFolderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new MoveFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasRootFolderReachedChildrenLimit::class,
                'hasRootFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithCallback(
                HasFolderReachedAncestorLimit::class,
                'hasFolderReachedAncestorLimit',
                function (string $folderId) use ($command) {
                    if ($folderId === $command->destinationFolderId) {
                        return true;
                    }

                    return false;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsFolderAncestorOfDestinationFolder::class,
                'isFolderAncestorOfDestinationFolder',
                false
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\DestinationFolderHasTooManyChildren
     */
    public function testDestinationFolderHasTooManyChildren(): void
    {
        $command = new MoveFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->destinationFolderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new MoveFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasRootFolderReachedChildrenLimit::class,
                'hasRootFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedAncestorLimit::class,
                'hasFolderReachedAncestorLimit',
                false
            ),
            $this->mockForCommandHandlerWithCallback(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                function (string $folderId) use ($command) {
                    if ($folderId === $command->destinationFolderId) {
                        return true;
                    }

                    return false;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                IsFolderAncestorOfDestinationFolder::class,
                'isFolderAncestorOfDestinationFolder',
                false
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\MoveFolder\FolderIsAncestorOfDestinationFolder
     */
    public function testFolderIsAncestorOfDestinationFolder(): void
    {
        $command = new MoveFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->destinationFolderId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new MoveFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasRootFolderReachedChildrenLimit::class,
                'hasRootFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedAncestorLimit::class,
                'hasFolderReachedAncestorLimit',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                false
            ),
            $this->mockForCommandHandlerWithCallback(
                IsFolderAncestorOfDestinationFolder::class,
                'isFolderAncestorOfDestinationFolder',
                function (string $folderId, string $destinationFolderId) use ($command) {
                    if (
                        $folderId === $command->folderId &&
                        $destinationFolderId === $command->destinationFolderId
                    ) {
                        return true;
                    }

                    return false;
                }
            )
        );

        $handler->handle($command);
    }
}
