<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\Primitive\PrimitiveTransformation\Hash\SameHashForTwoStringsRegardlessOfOrder;

class ContactListItem
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var ActiveContact|null
     */
    private $activeContact;

    /**
     * @var PendingContact|null
     */
    private $pendingContact;

    /**
     * @var InactiveContact|null
     */
    private $inactiveContact;

    private function __construct()
    {
    }

    public function getCombinedContactsHash(): string
    {
        return $this->id;
    }

    public function getActiveContact(): ?ActiveContact
    {
        return $this->activeContact;
    }

    public function getPendingContact(): ?PendingContact
    {
        return $this->pendingContact;
    }

    public function getInactiveContact(): ?InactiveContact
    {
        return $this->inactiveContact;
    }

    public function changeFromAnotherContactListItem(ContactListItem $contactListItem): self
    {
        $this->id = $contactListItem->getCombinedContactsHash();
        $this->activeContact = $contactListItem->getActiveContact();
        $this->pendingContact = $contactListItem->getPendingContact();
        $this->inactiveContact = $contactListItem->getInactiveContact();

        return $this;
    }

    public static function fromActiveContact(ActiveContact $activeContact): self
    {
        $contactListItem = new self();
        $contactListItem->id = SameHashForTwoStringsRegardlessOfOrder::hash(
            $activeContact->getFirstContactId(),
            $activeContact->getSecondContactId()
        );
        $contactListItem->activeContact = $activeContact;
        $contactListItem->pendingContact = null;
        $contactListItem->inactiveContact = null;

        return $contactListItem;
    }

    public static function fromPendingContact(PendingContact $pendingContact): self
    {
        $contactListItem = new self();
        $contactListItem->id = SameHashForTwoStringsRegardlessOfOrder::hash(
            $pendingContact->getRequestedContactId(),
            $pendingContact->getRequesterContactId()
        );
        $contactListItem->activeContact = null;
        $contactListItem->pendingContact = $pendingContact;
        $contactListItem->inactiveContact = null;

        return $contactListItem;
    }

    public static function fromInactiveContact(InactiveContact $inactiveContact): self
    {
        $contactListItem = new self();
        $contactListItem->id = SameHashForTwoStringsRegardlessOfOrder::hash(
            $inactiveContact->getFirstContactId(),
            $inactiveContact->getSecondContactId()
        );
        $contactListItem->activeContact = null;
        $contactListItem->pendingContact = null;
        $contactListItem->inactiveContact = $inactiveContact;

        return $contactListItem;
    }
}
