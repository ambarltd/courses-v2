<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\CommandHandler\CreateFolder;

use Galeas\Api\BoundedContext\Library\Folder\Command\CreateFolder;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Primitive\PrimitiveValidation\FolderName\FolderNameValidator;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class CreateFolderHandler
{
    /**
     * @var EventStore
     */
    private $eventStore;

    /**
     * @var Queue
     */
    private $queue;

    /**
     * @var DoesFolderExistAndIsItOwnedByUser
     */
    private $doesFolderExistAndIsItOwnedByUser;

    /**
     * @var HasRootFolderReachedChildrenLimit
     */
    private $hasRootFolderReachedChildrenLimit;

    /**
     * @var HasFolderReachedAncestorLimit
     */
    private $hasFolderReachedAncestorLimit;

    /**
     * @var HasFolderReachedChildrenLimit
     */
    private $hasFolderReachedChildrenLimit;

    public function __construct(
        EventStore $eventStore,
        Queue $queue,
        DoesFolderExistAndIsItOwnedByUser $doesFolderExistAndIsItOwnedByUser,
        HasRootFolderReachedChildrenLimit $hasRootFolderReachedChildrenLimit,
        HasFolderReachedAncestorLimit $hasFolderReachedAncestorLimit,
        HasFolderReachedChildrenLimit $hasFolderReachedChildrenLimit
    ) {
        $this->eventStore = $eventStore;
        $this->queue = $queue;
        $this->doesFolderExistAndIsItOwnedByUser = $doesFolderExistAndIsItOwnedByUser;
        $this->hasRootFolderReachedChildrenLimit = $hasRootFolderReachedChildrenLimit;
        $this->hasFolderReachedAncestorLimit = $hasFolderReachedAncestorLimit;
        $this->hasFolderReachedChildrenLimit = $hasFolderReachedChildrenLimit;
    }

    /**
     * @throws InvalidFolderName|ParentFolderNotOwned|ParentFolderHasTooManyChildren
     * @throws RootFolderHasTooManyChildren|ParentFolderHasTooManyAncestors
     * @throws InvalidId|EventStoreCannotWrite|QueuingFailure|ProjectionCannotRead|QueuingFailure
     */
    public function handle(CreateFolder $command): array
    {
        $this->eventStore->beginTransaction();

        if (false === FolderNameValidator::isValid($command->name)) {
            throw new InvalidFolderName();
        }

        if (
            null === $command->parentId &&
            true === $this->hasRootFolderReachedChildrenLimit->hasRootFolderReachedChildrenLimit($command->authorizerId)
        ) {
            throw new RootFolderHasTooManyChildren();
        }

        if (
            null !== $command->parentId &&
            false === $this->doesFolderExistAndIsItOwnedByUser->doesFolderExistAndIsItOwnedByUser($command->parentId, $command->authorizerId)
        ) {
            throw new ParentFolderNotOwned();
        }

        if (
            null !== $command->parentId &&
            true === $this->hasFolderReachedAncestorLimit->hasFolderReachedAncestorLimit($command->parentId)
        ) {
            throw new ParentFolderHasTooManyAncestors();
        }

        if (
            null !== $command->parentId &&
            true === $this->hasFolderReachedChildrenLimit->hasFolderReachedChildrenLimit($command->parentId)
        ) {
            throw new ParentFolderHasTooManyChildren();
        }

        $parentId = null;
        if (null !== $command->parentId) {
            $parentId = Id::fromId($command->parentId);
        }

        $folderCreated = FolderCreated::fromProperties(
            Id::fromId($command->authorizerId),
            $command->metadata,
            $command->name,
            $parentId
        );

        $this->eventStore->save($folderCreated);
        $this->eventStore->completeTransaction();
        $this->queue->enqueue($folderCreated);

        return [
            'folderId' => $folderCreated->aggregateId()->id(),
        ];
    }
}
