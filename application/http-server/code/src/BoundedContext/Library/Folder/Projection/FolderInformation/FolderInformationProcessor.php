<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Iterator\Iterator;
use Doctrine\ODM\MongoDB\MongoDBException;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderDeleted;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderMoved;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderRenamed;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\ProjectionEventProcessor;

class FolderInformationProcessor implements ProjectionEventProcessor
{
    /**
     * @var DocumentManager
     */
    private $projectionDocumentManager;

    public function __construct(DocumentManager $projectionDocumentManager)
    {
        $this->projectionDocumentManager = $projectionDocumentManager;
    }

    /**
     * {@inheritdoc}
     */
    public function process(Event $event): void
    {
        try {
            if ($event instanceof FolderCreated) {
                $folderInformation =
                    FolderInformation::fromProperties(
                        $event->aggregateId()->id(),
                        $event->name(),
                        null === $event->parent() ? null : $event->parent()->id(),
                        $event->ownerId()->id()
                    );
            } elseif ($event instanceof FolderRenamed) {
                $folderInformation = $this->findOneById($event->aggregateId()->id());

                $folderInformation->updateName($event->name());
            } elseif ($event instanceof FolderMoved) {
                $folderInformation = $this->findOneById($event->aggregateId()->id());

                $folderInformation->updateParent(null !== $event->destinationId() ? $event->destinationId()->id() : null);
            } elseif ($event instanceof FolderDeleted) {
                $this->deleteAllDescendants($event->aggregateId()->id());

                return;
            } else {
                return;
            }

            $this->projectionDocumentManager->persist($folderInformation);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }

    /**
     * @throws \Exception
     */
    private function findOneById(string $id): FolderInformation
    {
        $folderInformation = $this->projectionDocumentManager
            ->createQueryBuilder(FolderInformation::class)
            ->field('id')->equals($id)
            ->getQuery()
            ->getSingleResult();

        if ($folderInformation instanceof FolderInformation) {
            return $folderInformation;
        }

        throw new \Exception();
    }

    /**
     * @throws MongoDBException|\Exception
     */
    private function deleteAllDescendants(string $id): void
    {
        $allDescendantIds = $this->descendantIdsOfFolderIds([$id], [$id]);

        $this->projectionDocumentManager
            ->createQueryBuilder(FolderInformation::class)
            ->remove()
            ->field('id')->in($allDescendantIds)
            ->getQuery()
            ->execute();
    }

    /**
     * @param string[] $folderIds
     * @param string[] $accumulatedFolderIds
     *
     * @throws MongoDBException|\Exception
     */
    private function descendantIdsOfFolderIds(array $folderIds, array $accumulatedFolderIds): array
    {
        if ([] === $folderIds) {
            return $accumulatedFolderIds;
        }
        $childrenIds = $this->childrenIdsOfFolderIds($folderIds);

        $accumulatedFolderIds = array_merge($accumulatedFolderIds, $childrenIds);

        return $this->descendantIdsOfFolderIds($childrenIds, $accumulatedFolderIds);
    }

    /**
     * @param string[] $folderIds
     *
     * @return string[]
     *
     * @throws MongoDBException|\Exception
     */
    private function childrenIdsOfFolderIds(array $folderIds): array
    {
        $folders = $this->projectionDocumentManager
            ->createQueryBuilder(FolderInformation::class)
            ->field('parent')->in($folderIds)
            ->getQuery()
            ->execute();

        if ($folders instanceof Iterator) {
            $folders = $folders->toArray();
        } else {
            throw new \Exception();
        }

        $ids = [];
        foreach ($folders as $folder) {
            if ($folder instanceof FolderInformation) {
                $ids[] = $folder->folderId();
            }
        }

        return $ids;
    }
}
