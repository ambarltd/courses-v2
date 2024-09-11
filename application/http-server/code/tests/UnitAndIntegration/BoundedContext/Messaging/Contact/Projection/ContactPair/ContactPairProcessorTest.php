<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactPair;

use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair\ContactPair;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair\ContactPairProcessor;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ContactPairProcessorTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testProcessContactRequested(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $contactRequested1 = ContactRequested::fromContacts(
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );
        $contactRequested2 = ContactRequested::fromContacts(
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequested2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequested1);
        $contactPairProcessor->process($contactRequested1); // test idempotency
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequested1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequested1->requestedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequested1->requesterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequested2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequested2);
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequested1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequested1->requestedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequested1->requesterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequested2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequested2->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested2->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequested2->requestedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested2->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequested2->requesterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequested2->aggregateId()->id()
            )[0]->getSecondContactId()
        );
    }

    /**
     * @test
     */
    public function testProcessContactRequestAccepted(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $aggregateId1 = Id::createNew();
        $aggregateId2 = Id::createNew();
        $contactRequestAccepted1 = ContactRequestAccepted::fromContacts(
            $aggregateId1,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );
        $contactRequestAccepted2 = ContactRequestAccepted::fromContacts(
            $aggregateId2,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestAccepted2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestAccepted1);
        $contactPairProcessor->process($contactRequestAccepted1); // test idempotency
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestAccepted1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestAccepted1->acceptedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestAccepted1->accepterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestAccepted2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestAccepted2);
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestAccepted1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestAccepted1->acceptedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestAccepted1->accepterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestAccepted2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestAccepted2->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted2->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestAccepted2->acceptedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted2->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestAccepted2->accepterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestAccepted2->aggregateId()->id()
            )[0]->getSecondContactId()
        );
    }

    /**
     * @test
     */
    public function testProcessContactRequestRejected(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $aggregateId1 = Id::createNew();
        $aggregateId2 = Id::createNew();
        $contactRequestRejected1 = ContactRequestRejected::fromContacts(
            $aggregateId1,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );
        $contactRequestRejected2 = ContactRequestRejected::fromContacts(
            $aggregateId2,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestRejected2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestRejected1);
        $contactPairProcessor->process($contactRequestRejected1); // test idempotency
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestRejected1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestRejected1->rejectedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestRejected1->rejecterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestRejected2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestRejected2);
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestRejected1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestRejected1->rejectedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestRejected1->rejecterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestRejected2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestRejected2->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected2->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestRejected2->rejectedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected2->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestRejected2->rejecterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestRejected2->aggregateId()->id()
            )[0]->getSecondContactId()
        );
    }

    /**
     * @test
     */
    public function testProcessContactRequestCancelled(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $aggregateId1 = Id::createNew();
        $aggregateId2 = Id::createNew();
        $contactRequestCancelled1 = ContactRequestCancelled::fromContacts(
            $aggregateId1,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );
        $contactRequestCancelled2 = ContactRequestCancelled::fromContacts(
            $aggregateId2,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestCancelled2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestCancelled1);
        $contactPairProcessor->process($contactRequestCancelled1); // test idempotency
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestCancelled1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestCancelled1->cancelledContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestCancelled1->cancellerContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestCancelled2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestCancelled2);
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestCancelled1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestCancelled1->cancelledContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestCancelled1->cancellerContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestCancelled2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestCancelled2->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled2->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestCancelled2->cancelledContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled2->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestCancelled2->cancellerContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestCancelled2->aggregateId()->id()
            )[0]->getSecondContactId()
        );
    }

    /**
     * @test
     */
    public function testProcessContactDeleted(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $aggregateId1 = Id::createNew();
        $aggregateId2 = Id::createNew();
        $contactDeleted1 = ContactDeleted::fromContacts(
            $aggregateId1,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );
        $contactDeleted2 = ContactDeleted::fromContacts(
            $aggregateId2,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactDeleted2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactDeleted1);
        $contactPairProcessor->process($contactDeleted1); // test idempotency
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactDeleted1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactDeleted1->deletedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactDeleted1->deleterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactDeleted2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactDeleted2);
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactDeleted1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactDeleted1->deletedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactDeleted1->deleterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactDeleted2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactDeleted2->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted2->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactDeleted2->deletedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted2->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactDeleted2->deleterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactDeleted2->aggregateId()->id()
            )[0]->getSecondContactId()
        );
    }

    /**
     * @test
     */
    public function testProcessContactRequestedAgain(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $aggregateId1 = Id::createNew();
        $aggregateId2 = Id::createNew();
        $contactRequestedAgain1 = ContactRequestedAgain::fromContacts(
            $aggregateId1,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );
        $contactRequestedAgain2 = ContactRequestedAgain::fromContacts(
            $aggregateId2,
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestedAgain2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestedAgain1);
        $contactPairProcessor->process($contactRequestedAgain1); // test idempotency
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestedAgain1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestedAgain1->requestedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestedAgain1->requesterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            0,
            $this->findContactPairByAggregateId(
                $contactRequestedAgain2->aggregateId()->id()
            )
        );

        $contactPairProcessor->process($contactRequestedAgain2);
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestedAgain1->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestedAgain1->requestedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestedAgain1->requesterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain1->aggregateId()->id()
            )[0]->getSecondContactId()
        );
        Assert::assertCount(
            1,
            $this->findContactPairByAggregateId(
                $contactRequestedAgain2->aggregateId()->id()
            )
        );
        Assert::assertEquals(
            $contactRequestedAgain2->aggregateId()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain2->aggregateId()->id()
            )[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestedAgain2->requestedContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain2->aggregateId()->id()
            )[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestedAgain2->requesterContact()->id(),
            $this->findContactPairByAggregateId(
                $contactRequestedAgain2->aggregateId()->id()
            )[0]->getSecondContactId()
        );
    }

    /**
     * @test
     */
    public function testOneAggregatePerPair(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $contactRequestAccepted = ContactRequestAccepted::fromContacts(
            Id::createNew(),
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        $contactRequestedAcceptedWithDifferentAggregateId = ContactRequestAccepted::fromContacts(
            Id::createNew(),
            Id::createNew(),
            [],
            $contactRequestAccepted->accepterContact(),
            $contactRequestAccepted->acceptedContact()
        );

        Assert::assertCount(
            0,
            $this->findAllContactPairs()
        );

        $contactPairProcessor->process($contactRequestAccepted);
        $contactPairProcessor->process($contactRequestedAcceptedWithDifferentAggregateId);

        Assert::assertCount(
            1,
            $this->findAllContactPairs()
        );
        Assert::assertEquals(
            $contactRequestedAcceptedWithDifferentAggregateId->aggregateId()->id(),
            $this->findAllContactPairs()[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestedAcceptedWithDifferentAggregateId->acceptedContact()->id(),
            $this->findAllContactPairs()[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestedAcceptedWithDifferentAggregateId->accepterContact()->id(),
            $this->findAllContactPairs()[0]->getSecondContactId()
        );
    }

    /**
     * @test
     */
    public function testOnePairPerAggregate(): void
    {
        $contactPairProcessor = $this->getContainer()
            ->get(ContactPairProcessor::class);

        $contactRequestAccepted = ContactRequestAccepted::fromContacts(
            Id::createNew(),
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        $contactRequestedAcceptedWithDifferentAccepterIdAndAcceptedId = ContactRequestAccepted::fromContacts(
            $contactRequestAccepted->aggregateId(),
            Id::createNew(),
            [],
            Id::createNew(),
            Id::createNew()
        );

        Assert::assertCount(
            0,
            $this->findAllContactPairs()
        );

        $contactPairProcessor->process($contactRequestAccepted);
        $contactPairProcessor->process($contactRequestedAcceptedWithDifferentAccepterIdAndAcceptedId);

        Assert::assertCount(
            1,
            $this->findAllContactPairs()
        );
        Assert::assertEquals(
            $contactRequestedAcceptedWithDifferentAccepterIdAndAcceptedId->aggregateId()->id(),
            $this->findAllContactPairs()[0]->getContactId()
        );
        Assert::assertEquals(
            $contactRequestedAcceptedWithDifferentAccepterIdAndAcceptedId->acceptedContact()->id(),
            $this->findAllContactPairs()[0]->getFirstContactId()
        );
        Assert::assertEquals(
            $contactRequestedAcceptedWithDifferentAccepterIdAndAcceptedId->accepterContact()->id(),
            $this->findAllContactPairs()[0]->getSecondContactId()
        );
    }

    /**
     * @return ContactPair[]
     */
    private function findContactPairByAggregateId(string $aggregateId): array
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(ContactPair::class)
                ->field('id')->equals($aggregateId)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }

    /**
     * @return ContactPair[]
     */
    private function findAllContactPairs(): array
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(ContactPair::class)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
