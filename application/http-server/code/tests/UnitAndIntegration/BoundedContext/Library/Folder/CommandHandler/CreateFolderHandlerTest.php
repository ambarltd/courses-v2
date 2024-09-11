<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\CommandHandler;

use Galeas\Api\BoundedContext\Library\Folder\Command\CreateFolder;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\CreateFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\DoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\HasFolderReachedAncestorLimit;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\HasFolderReachedChildrenLimit;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\HasRootFolderReachedChildrenLimit;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\FolderName\InvalidFolderNames;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\FolderName\ValidFolderNames;

class CreateFolderHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $command = new CreateFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->name = ValidFolderNames::listValidFolderNames()[0];
        $command->parentId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new CreateFolderHandler(
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
            )
        );

        $response = $handler->handle($command);

        /** @var FolderCreated $storedEvent */
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
            $command->name,
            $storedEvent->name()
        );
        if (null === $storedEvent->parent()) {
            throw new \Exception('Parent should not be null');
        }
        Assert::assertEquals(
            $command->parentId,
            $storedEvent->parent()->id()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
        Assert::assertEquals(
            [
                'folderId' => $storedEvent->aggregateId()->id(),
            ],
            $response
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\InvalidFolderName
     */
    public function testInvalidName(): void
    {
        $command = new CreateFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->name = InvalidFolderNames::listInvalidFolderNames()[0];
        $command->parentId = null;
        $command->metadata = $this->mockMetadata();

        $handler = new CreateFolderHandler(
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
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\RootFolderHasTooManyChildren
     */
    public function testRootFolderHasTooManyChildren(): void
    {
        $command = new CreateFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->name = ValidFolderNames::listValidFolderNames()[0];
        $command->parentId = null;
        $command->metadata = $this->mockMetadata();

        $handler = new CreateFolderHandler(
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
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\ParentFolderNotOwned
     */
    public function testParentFolderNotOwned(): void
    {
        $command = new CreateFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->name = ValidFolderNames::listValidFolderNames()[0];
        $command->parentId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new CreateFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                function (string $folderId, string $userId) use ($command): bool {
                    if (
                        $folderId === $command->parentId &&
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
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\ParentFolderHasTooManyAncestors
     */
    public function testParentFolderHasTooManyAncestors(): void
    {
        $command = new CreateFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->name = ValidFolderNames::listValidFolderNames()[0];
        $command->parentId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new CreateFolderHandler(
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
                function (string $folderId) use ($command): bool {
                    if ($folderId === $command->parentId) {
                        return true;
                    }

                    return false;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                HasFolderReachedChildrenLimit::class,
                'hasFolderReachedChildrenLimit',
                false
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder\ParentFolderHasTooManyChildren
     */
    public function testParentFolderHasTooManyChildren(): void
    {
        $command = new CreateFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->name = ValidFolderNames::listValidFolderNames()[0];
        $command->parentId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new CreateFolderHandler(
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
                function (string $folderId) use ($command): bool {
                    if ($folderId === $command->parentId) {
                        return true;
                    }

                    return false;
                }
            )
        );

        $handler->handle($command);
    }
}
