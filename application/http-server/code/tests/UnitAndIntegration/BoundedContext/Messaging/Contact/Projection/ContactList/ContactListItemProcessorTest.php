<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactListItem;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactListItemProcessor;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\PendingContact;
use Galeas\Api\Common\Id\Id;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ContactListItemProcessorTest extends KernelTestBase
{
    /**
     * @test
     */
    public function testContactRequested(): void
    {
        $contactListItemProcessor = $this->getContainer()
            ->get(ContactListItemProcessor::class);

        $requestedId1 = Id::createNew();
        $requesterId1 = Id::createNew();
        $contactRequested1 = ContactRequested::fromContacts(
            $requesterId1,
            [],
            $requesterId1,
            $requestedId1
        );

        $contactListItemProcessor->process($contactRequested1);
        $contactListItemProcessor->process($contactRequested1); // test idempotency

        $this->assertEquals(
            [
                ContactListItem::fromPendingContact(
                    PendingContact::fromContacts(
                        $requestedId1->id(),
                        $requesterId1->id()
                    )
                ),
            ],
            $this->findAllContactListItems()
        );

        $requestedId2 = Id::createNew();
        $requesterId2 = Id::createNew();
        $contactRequested2 = ContactRequested::fromContacts(
            $requesterId2,
            [],
            $requesterId2,
            $requestedId2
        );

        $contactListItemProcessor->process($contactRequested2);

        $this->assertEquals(
            $this->sortContactListItemsByCombinedContactsHash(
                [
                    ContactListItem::fromPendingContact(
                        PendingContact::fromContacts(
                            $requestedId1->id(),
                            $requesterId1->id()
                        )
                    ),
                    ContactListItem::fromPendingContact(
                        PendingContact::fromContacts(
                            $requestedId2->id(),
                            $requesterId2->id()
                        )
                    ),
                ]
            ),
            $this->sortContactListItemsByCombinedContactsHash(
                $this->findAllContactListItems()
            )
        );
    }

    /**
     * @test
     */
    public function testContactRequestCancelled(): void
    {
        $contactAggregateId = Id::createNew();

        $contactListItemProcessor = $this->getContainer()
            ->get(ContactListItemProcessor::class);

        $cancelledId1 = Id::createNew();
        $cancellerId1 = Id::createNew();
        $contactRequestedCancelled1 = ContactRequestCancelled::fromContacts(
            $contactAggregateId,
            $cancellerId1,
            [],
            $cancellerId1,
            $cancelledId1
        );

        $contactListItemProcessor->process($contactRequestedCancelled1);
        $contactListItemProcessor->process($contactRequestedCancelled1); // test idempotency

        $this->assertEquals(
            [
                ContactListItem::fromInactiveContact(
                    InactiveContact::fromContacts(
                        $cancelledId1->id(),
                        $cancellerId1->id()
                    )
                ),
            ],
            $this->findAllContactListItems()
        );

        $cancelledId2 = Id::createNew();
        $cancellerId2 = Id::createNew();
        $contactRequestedCancelled2 = ContactRequestCancelled::fromContacts(
            $contactAggregateId,
            $cancellerId2,
            [],
            $cancellerId2,
            $cancelledId2
        );

        $contactListItemProcessor->process($contactRequestedCancelled2);

        $this->assertEquals(
            $this->sortContactListItemsByCombinedContactsHash(
                [
                    ContactListItem::fromInactiveContact(
                        InactiveContact::fromContacts(
                            $cancelledId1->id(),
                            $cancellerId1->id()
                        )
                    ),
                    ContactListItem::fromInactiveContact(
                        InactiveContact::fromContacts(
                            $cancelledId2->id(),
                            $cancellerId2->id()
                        )
                    ),
                ]
            ),
            $this->sortContactListItemsByCombinedContactsHash(
                $this->findAllContactListItems()
            )
        );
    }

    /**
     * @test
     */
    public function testContactRequestRejected(): void
    {
        $contactAggregateId = Id::createNew();

        $contactListItemProcessor = $this->getContainer()
            ->get(ContactListItemProcessor::class);

        $rejectedId1 = Id::createNew();
        $rejecterId1 = Id::createNew();
        $contactRequestedRejected1 = ContactRequestRejected::fromContacts(
            $contactAggregateId,
            $rejecterId1,
            [],
            $rejecterId1,
            $rejectedId1
        );

        $contactListItemProcessor->process($contactRequestedRejected1);
        $contactListItemProcessor->process($contactRequestedRejected1); // test idempotency

        $this->assertEquals(
            [
                ContactListItem::fromInactiveContact(
                    InactiveContact::fromContacts(
                        $rejectedId1->id(),
                        $rejecterId1->id()
                    )
                ),
            ],
            $this->findAllContactListItems()
        );

        $rejectedId2 = Id::createNew();
        $rejecterId2 = Id::createNew();
        $contactRequestedRejected2 = ContactRequestRejected::fromContacts(
            $contactAggregateId,
            $rejecterId2,
            [],
            $rejecterId2,
            $rejectedId2
        );

        $contactListItemProcessor->process($contactRequestedRejected2);

        $this->assertEquals(
            $this->sortContactListItemsByCombinedContactsHash(
                [
                    ContactListItem::fromInactiveContact(
                        InactiveContact::fromContacts(
                            $rejectedId1->id(),
                            $rejecterId1->id()
                        )
                    ),
                    ContactListItem::fromInactiveContact(
                        InactiveContact::fromContacts(
                            $rejectedId2->id(),
                            $rejecterId2->id()
                        )
                    ),
                ]
            ),
            $this->sortContactListItemsByCombinedContactsHash(
                $this->findAllContactListItems()
            )
        );
    }

    /**
     * @test
     */
    public function testContactRequestAccepted(): void
    {
        $contactAggregateId = Id::createNew();

        $contactListItemProcessor = $this->getContainer()
            ->get(ContactListItemProcessor::class);

        $acceptedId1 = Id::createNew();
        $accepterId1 = Id::createNew();
        $contactRequestedAccepted1 = ContactRequestAccepted::fromContacts(
            $contactAggregateId,
            $accepterId1,
            [],
            $accepterId1,
            $acceptedId1
        );

        $contactListItemProcessor->process($contactRequestedAccepted1);
        $contactListItemProcessor->process($contactRequestedAccepted1); // test idempotency

        $this->assertEquals(
            [
                ContactListItem::fromActiveContact(
                    ActiveContact::fromContacts(
                        $acceptedId1->id(),
                        $accepterId1->id()
                    )
                ),
            ],
            $this->findAllContactListItems()
        );

        $acceptedId2 = Id::createNew();
        $accepterId2 = Id::createNew();
        $contactRequestedAccepted2 = ContactRequestAccepted::fromContacts(
            $contactAggregateId,
            $accepterId2,
            [],
            $accepterId2,
            $acceptedId2
        );

        $contactListItemProcessor->process($contactRequestedAccepted2);

        $this->assertEquals(
            $this->sortContactListItemsByCombinedContactsHash(
                [
                    ContactListItem::fromActiveContact(
                        ActiveContact::fromContacts(
                            $acceptedId1->id(),
                            $accepterId1->id()
                        )
                    ),
                    ContactListItem::fromActiveContact(
                        ActiveContact::fromContacts(
                            $acceptedId2->id(),
                            $accepterId2->id()
                        )
                    ),
                ]
            ),
            $this->sortContactListItemsByCombinedContactsHash(
                $this->findAllContactListItems()
            )
        );
    }

    /**
     * @test
     */
    public function testContactDeleted(): void
    {
        $contactAggregateId = Id::createNew();

        $contactListItemProcessor = $this->getContainer()
            ->get(ContactListItemProcessor::class);

        $deletedId1 = Id::createNew();
        $deleterId1 = Id::createNew();
        $contactRequestedRejected1 = ContactDeleted::fromContacts(
            $contactAggregateId,
            $deleterId1,
            [],
            $deleterId1,
            $deletedId1
        );

        $contactListItemProcessor->process($contactRequestedRejected1);
        $contactListItemProcessor->process($contactRequestedRejected1); // test idempotency

        $this->assertEquals(
            [
                ContactListItem::fromInactiveContact(
                    InactiveContact::fromContacts(
                        $deletedId1->id(),
                        $deleterId1->id()
                    )
                ),
            ],
            $this->findAllContactListItems()
        );

        $deletedId2 = Id::createNew();
        $deleterId2 = Id::createNew();
        $contactRequestedRejected2 = ContactDeleted::fromContacts(
            $contactAggregateId,
            $deleterId2,
            [],
            $deleterId2,
            $deletedId2
        );

        $contactListItemProcessor->process($contactRequestedRejected2);

        $this->assertEquals(
            $this->sortContactListItemsByCombinedContactsHash(
                [
                    ContactListItem::fromInactiveContact(
                        InactiveContact::fromContacts(
                            $deletedId1->id(),
                            $deleterId1->id()
                        )
                    ),
                    ContactListItem::fromInactiveContact(
                        InactiveContact::fromContacts(
                            $deletedId2->id(),
                            $deleterId2->id()
                        )
                    ),
                ]
            ),
            $this->sortContactListItemsByCombinedContactsHash(
                $this->findAllContactListItems()
            )
        );
    }

    /**
     * @test
     */
    public function testContactRequestedAgain(): void
    {
        $contactAggregateId = Id::createNew();

        $contactListItemProcessor = $this->getContainer()
            ->get(ContactListItemProcessor::class);

        $requestedId1 = Id::createNew();
        $requesterId1 = Id::createNew();
        $contactRequestedAgain1 = ContactRequestedAgain::fromContacts(
            $contactAggregateId,
            $requesterId1,
            [],
            $requesterId1,
            $requestedId1
        );

        $contactListItemProcessor->process($contactRequestedAgain1);
        $contactListItemProcessor->process($contactRequestedAgain1); // test idempotency

        $this->assertEquals(
            [
                ContactListItem::fromPendingContact(
                    PendingContact::fromContacts(
                        $requestedId1->id(),
                        $requesterId1->id()
                    )
                ),
            ],
            $this->findAllContactListItems()
        );

        $requestedId2 = Id::createNew();
        $requesterId2 = Id::createNew();
        $contactRequestedAgain2 = ContactRequestedAgain::fromContacts(
            $contactAggregateId,
            $requesterId2,
            [],
            $requesterId2,
            $requestedId2
        );

        $contactListItemProcessor->process($contactRequestedAgain2);

        $this->assertEquals(
            $this->sortContactListItemsByCombinedContactsHash(
                [
                    ContactListItem::fromPendingContact(
                        PendingContact::fromContacts(
                            $requestedId1->id(),
                            $requesterId1->id()
                        )
                    ),
                    ContactListItem::fromPendingContact(
                        PendingContact::fromContacts(
                            $requestedId2->id(),
                            $requesterId2->id()
                        )
                    ),
                ]
            ),
            $this->sortContactListItemsByCombinedContactsHash(
                $this->findAllContactListItems()
            )
        );
    }

    /**
     * @test
     */
    public function testOneItemPerContactPair(): void
    {
        $contactAggregateId = Id::createNew();

        $contactListItemProcessor = $this->getContainer()
            ->get(ContactListItemProcessor::class);

        $acceptedId1 = Id::createNew();
        $accepterId1 = Id::createNew();
        $contactRequestedAccepted1 = ContactRequestAccepted::fromContacts(
            $contactAggregateId,
            $accepterId1,
            [],
            $accepterId1,
            $acceptedId1
        );

        $contactListItemProcessor->process($contactRequestedAccepted1);

        $this->assertEquals(
            [
                ContactListItem::fromActiveContact(
                    ActiveContact::fromContacts(
                        $acceptedId1->id(),
                        $accepterId1->id()
                    )
                ),
            ],
            $this->findAllContactListItems()
        );

        $acceptedId2 = $accepterId1;
        $accepterId2 = $acceptedId1;
        $contactRequestedAccepted2 = ContactRequestAccepted::fromContacts(
            $contactAggregateId,
            $accepterId2,
            [],
            $accepterId2,
            $acceptedId2
        );

        $contactListItemProcessor->process($contactRequestedAccepted2);

        $this->assertEquals(
            $this->sortContactListItemsByCombinedContactsHash(
                [
                    ContactListItem::fromActiveContact(
                        ActiveContact::fromContacts(
                            $acceptedId2->id(),
                            $accepterId2->id()
                        )
                    ),
                ]
            ),
            $this->sortContactListItemsByCombinedContactsHash(
                $this->findAllContactListItems()
            )
        );
    }

    /**
     * @return ContactListItem[]
     */
    private function findAllContactListItems()
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(ContactListItem::class)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }

    /**
     * @param ContactListItem[] $contactListItems
     */
    private function sortContactListItemsByCombinedContactsHash(array $contactListItems): array
    {
        usort(
            $contactListItems,
            function (
                ContactListItem $contactListItemA,
                ContactListItem $contactListItemB
            ) {
                return $contactListItemA->getCombinedContactsHash() > $contactListItemB->getCombinedContactsHash();
            }
        );

        return $contactListItems;
    }
}
