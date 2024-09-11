<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\CommandHandler;

use Galeas\Api\BoundedContext\Library\Folder\Command\RenameFolder;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder\DoesFolderExistAndIsItOwnedByUser;
use Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder\RenameFolderHandler;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderRenamed;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\FolderName\InvalidFolderNames;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\FolderName\ValidFolderNames;

class RenameFolderHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $command = new RenameFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->name = ValidFolderNames::listValidFolderNames()[0];
        $command->metadata = $this->mockMetadata();

        $handler = new RenameFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            )
        );

        $handler->handle($command);

        /** @var FolderRenamed $storedEvent */
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
            $command->name,
            $storedEvent->name()
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
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder\InvalidFolderName
     */
    public function testInvalidName(): void
    {
        $command = new RenameFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->name = InvalidFolderNames::listInvalidFolderNames()[0];
        $command->metadata = $this->mockMetadata();

        $handler = new RenameFolderHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesFolderExistAndIsItOwnedByUser::class,
                'doesFolderExistAndIsItOwnedByUser',
                true
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Library\Folder\CommandHandler\RenameFolder\FolderNotOwned
     */
    public function testFolderNotOwned(): void
    {
        $command = new RenameFolder();
        $command->authorizerId = Id::createNew()->id();
        $command->folderId = Id::createNew()->id();
        $command->name = ValidFolderNames::listValidFolderNames()[0];
        $command->metadata = $this->mockMetadata();

        $handler = new RenameFolderHandler(
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
