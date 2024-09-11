<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactListItem;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\PendingContact;
use Galeas\Api\Primitive\PrimitiveTransformation\Hash\SameHashForTwoStringsRegardlessOfOrder;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactListItemTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testFromInactiveContact(): void
    {
        $inactiveContact = $this->sampleInactiveContact();
        $contactListItem = ContactListItem::fromInactiveContact(
            $inactiveContact
        );

        Assert::assertEquals(
            null,
            $contactListItem->getActiveContact()
        );
        Assert::assertEquals(
            $inactiveContact,
            $contactListItem->getInactiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getPendingContact()
        );
        Assert::assertEquals(
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $inactiveContact->getFirstContactId(),
                $inactiveContact->getSecondContactId()
            ),
            $contactListItem->getCombinedContactsHash()
        );
    }

    /**
     * @test
     */
    public function testFromPendingContact(): void
    {
        $pendingContact = $this->samplePendingContact();
        $contactListItem = ContactListItem::fromPendingContact(
            $pendingContact
        );

        Assert::assertEquals(
            null,
            $contactListItem->getActiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getInactiveContact()
        );
        Assert::assertEquals(
            $pendingContact,
            $contactListItem->getPendingContact()
        );
        Assert::assertEquals(
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $pendingContact->getRequestedContactId(),
                $pendingContact->getRequesterContactId()
            ),
            $contactListItem->getCombinedContactsHash()
        );
    }

    /**
     * @test
     */
    public function testFromActiveContact(): void
    {
        $activeContact = $this->sampleActiveContact();
        $contactListItem = ContactListItem::fromActiveContact(
            $activeContact
        );

        Assert::assertEquals(
            $activeContact,
            $contactListItem->getActiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getInactiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getPendingContact()
        );
        Assert::assertEquals(
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $activeContact->getFirstContactId(),
                $activeContact->getSecondContactId()
            ),
            $contactListItem->getCombinedContactsHash()
        );
    }

    /**
     * @test
     */
    public function testChangeFromAnotherContactListItem(): void
    {
        $inactiveContact = $this->sampleInactiveContact();
        $pendingContact = $this->samplePendingContact();
        $activeContact = $this->sampleActiveContact();

        $contactListItem = ContactListItem::fromInactiveContact(
            $inactiveContact
        );

        $contactListItem->changeFromAnotherContactListItem(
            ContactListItem::fromPendingContact(
                $pendingContact
            )
        );
        Assert::assertEquals(
            null,
            $contactListItem->getActiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getInactiveContact()
        );
        Assert::assertEquals(
            $pendingContact,
            $contactListItem->getPendingContact()
        );
        Assert::assertEquals(
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $pendingContact->getRequestedContactId(),
                $pendingContact->getRequesterContactId()
            ),
            $contactListItem->getCombinedContactsHash()
        );

        $contactListItem->changeFromAnotherContactListItem(
            ContactListItem::fromActiveContact(
                $activeContact
            )
        );
        Assert::assertEquals(
            $activeContact,
            $contactListItem->getActiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getInactiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getPendingContact()
        );
        Assert::assertEquals(
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $activeContact->getFirstContactId(),
                $activeContact->getSecondContactId()
            ),
            $contactListItem->getCombinedContactsHash()
        );

        $contactListItem->changeFromAnotherContactListItem(
            ContactListItem::fromInactiveContact(
                $inactiveContact
            )
        );
        Assert::assertEquals(
            null,
            $contactListItem->getActiveContact()
        );
        Assert::assertEquals(
            $inactiveContact,
            $contactListItem->getInactiveContact()
        );
        Assert::assertEquals(
            null,
            $contactListItem->getPendingContact()
        );
        Assert::assertEquals(
            SameHashForTwoStringsRegardlessOfOrder::hash(
                $inactiveContact->getFirstContactId(),
                $inactiveContact->getSecondContactId()
            ),
            $contactListItem->getCombinedContactsHash()
        );
    }

    private function sampleInactiveContact(): InactiveContact
    {
        return InactiveContact::fromContacts(
            'inactive_first_user_id',
            'inactive_second_user_id'
        );
    }

    private function samplePendingContact(): PendingContact
    {
        return PendingContact::fromContacts(
            'pending_first_user_id',
            'pending_second_user_id'
        );
    }

    private function sampleActiveContact(): ActiveContact
    {
        return ActiveContact::fromContacts(
            'active_first_user_id',
            'active_second_user_id'
        );
    }
}
