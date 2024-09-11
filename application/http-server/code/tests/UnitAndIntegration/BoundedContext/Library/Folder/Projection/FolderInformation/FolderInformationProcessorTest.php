<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Library\Folder\Projection\FolderInformation;

use Galeas\Api\BoundedContext\Library\Folder\Event\FolderCreated;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderDeleted;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderMoved;
use Galeas\Api\BoundedContext\Library\Folder\Event\FolderRenamed;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformation;
use Galeas\Api\BoundedContext\Library\Folder\Projection\FolderInformation\FolderInformationProcessor;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class FolderInformationProcessorTest extends KernelTestBase
{
    /**
     * @test
     *
     * @throws \Exception
     */
    public function testProcessFolderCreatedEvent(): void
    {
        $folderCreated = FolderCreated::fromProperties(
            Id::createNew(),
            [1, 2, 3],
            'folder',
            null
        );

        $processorService = $this->getContainer()
            ->get(FolderInformationProcessor::class);
        $processorService->process($folderCreated);

        $repository = $this->getProjectionDocumentManager()->getRepository(FolderInformation::class);
        $folderInformationRecords = $repository->findAll();

        Assert::assertEquals(
            [
              FolderInformation::fromProperties(
                  $folderCreated->aggregateId()->id(),
                  $folderCreated->name(),
                  null !== $folderCreated->parent() ? $folderCreated->parent()->id() : null,
                  $folderCreated->ownerId()->id()
              ),
            ],
            $folderInformationRecords
        );
    }

    /**
     * @test
     */
    public function testProcessFolderRenamedEvent(): void
    {
        $folderId = Id::createNew();
        $parent = Id::createNew();
        $owner = Id::createNew();
        $folderInformation = FolderInformation::fromProperties(
            $folderId->id(),
            'folder_name',
            $parent->id(),
            $owner->id()
        );

        $documentManager = $this->getProjectionDocumentManager();
        $repository = $documentManager->getRepository(FolderInformation::class);
        $documentManager->persist($folderInformation);
        $documentManager->flush();

        $folderRenamed = FolderRenamed::fromProperties(
            $owner,
            [1, 2, 3],
            $folderId,
            'new_folder_name'
        );

        $processorService = $this->getContainer()
            ->get(FolderInformationProcessor::class);
        $processorService->process($folderRenamed);

        Assert::assertEquals(
            [
                FolderInformation::fromProperties(
                    $folderId->id(),
                    'new_folder_name',
                    $parent->id(),
                    $owner->id()
                ),
            ],
            $repository->findAll()
        );
    }

    /**
     * @test
     */
    public function testProcessFolderMoved(): void
    {
        $folderId = Id::createNew();
        $parent = Id::createNew();
        $owner = Id::createNew();
        $folderInformation = FolderInformation::fromProperties(
            $folderId->id(),
            'folder_name',
            $parent->id(),
            $owner->id()
        );

        $documentManager = $this->getProjectionDocumentManager();
        $repository = $documentManager->getRepository(FolderInformation::class);
        $documentManager->persist($folderInformation);
        $documentManager->flush();

        $destinationFolderId = Id::createNew();
        $folderMoved = FolderMoved::fromProperties(
            $owner,
            [1, 2, 3],
            $folderId,
            $destinationFolderId
        );

        $processorService = $this->getContainer()
            ->get(FolderInformationProcessor::class);
        $processorService->process($folderMoved);

        Assert::assertEquals(
            [
                FolderInformation::fromProperties(
                    $folderId->id(),
                    'folder_name',
                    $destinationFolderId->id(),
                    $owner->id()
                ),
            ],
            $repository->findAll()
        );
    }

    /**
     * @test
     */
    public function testProcessFolderDeleted(): void
    {
        $owner = Id::createNew()->id();
        //         _null_
        //        /      \
        //       a        b
        //       |        |
        //     __a1____   b1
        //    /   \    \
        //  a1a   a1b  a1c
        //   |          |
        // a1a1       a1c1
        //   |
        // a1a1a
        $a = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            null,
            $owner
        );
        $b = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            null,
            $owner
        );
        $a1 = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a->folderId(),
            $owner
        );
        $b1 = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $b->folderId(),
            $owner
        );
        $a1a = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1->folderId(),
            $owner
        );
        $a1b = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1->folderId(),
            $owner
        );
        $a1c = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1->folderId(),
            $owner
        );
        $a1a1 = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1a->folderId(),
            $owner
        );
        $a1c1 = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1c->folderId(),
            $owner
        );
        $a1a1a = FolderInformation::fromProperties(
            Id::createNew()->id(),
            'folderName',
            $a1a1->folderId(),
            $owner
        );

        $documentManager = $this->getProjectionDocumentManager();

        $documentManager->persist($a);
        $documentManager->persist($b);
        $documentManager->persist($a1);
        $documentManager->persist($b1);
        $documentManager->persist($a1a);
        $documentManager->persist($a1b);
        $documentManager->persist($a1c);
        $documentManager->persist($a1a1);
        $documentManager->persist($a1c1);
        $documentManager->persist($a1a1a);
        $documentManager->flush();

        $processorService = $this->getContainer()
            ->get(FolderInformationProcessor::class);

        $processorService->process(FolderDeleted::fromProperties(
            Id::fromId($owner),
            [1, 2, 3],
            Id::fromId($a1->folderId())
        ));
        $expectedFolders = [$a, $b, $b1];
        $repository = $documentManager->getRepository(FolderInformation::class);
        $foundFolders = $repository->findAll();
        Assert::assertEquals(
            $expectedFolders,
            $foundFolders
        );

        $processorService->process(FolderDeleted::fromProperties(
            Id::fromId($owner),
            [1, 2, 3],
            Id::fromId($b->folderId())
        ));
        $expectedFolders = [$a];
        $foundFolders = $repository->findAll();
        Assert::assertEquals(
            $expectedFolders,
            $foundFolders
        );
    }
}
