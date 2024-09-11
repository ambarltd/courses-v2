<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList;

use Doctrine\ODM\MongoDB\DocumentManager;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;
use Galeas\Api\Service\QueueProcessor\ProjectionEventProcessor;

class ContactListItemProcessor implements ProjectionEventProcessor
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
            if ($event instanceof ContactRequested) {
                $contactListItem = ContactListItem::fromPendingContact(
                    PendingContact::fromContacts(
                        $event->requestedContact()->id(),
                        $event->requesterContact()->id()
                    )
                );
                $combinedContactsHash = $contactListItem->getCombinedContactsHash();
            } elseif ($event instanceof ContactRequestRejected) {
                $contactListItem = ContactListItem::fromInactiveContact(
                    InactiveContact::fromContacts(
                        $event->rejectedContact()->id(),
                        $event->rejecterContact()->id()
                    )
                );
                $combinedContactsHash = $contactListItem->getCombinedContactsHash();
            } elseif ($event instanceof ContactRequestAccepted) {
                $contactListItem = ContactListItem::fromActiveContact(
                    ActiveContact::fromContacts(
                        $event->acceptedContact()->id(),
                        $event->accepterContact()->id()
                    )
                );
                $combinedContactsHash = $contactListItem->getCombinedContactsHash();
            } elseif ($event instanceof ContactRequestCancelled) {
                $contactListItem = ContactListItem::fromInactiveContact(
                    InactiveContact::fromContacts(
                        $event->cancelledContact()->id(),
                        $event->cancellerContact()->id()
                    )
                );
                $combinedContactsHash = $contactListItem->getCombinedContactsHash();
            } elseif ($event instanceof ContactDeleted) {
                $contactListItem = ContactListItem::fromInactiveContact(
                    InactiveContact::fromContacts(
                        $event->deletedContact()->id(),
                        $event->deleterContact()->id()
                    )
                );
                $combinedContactsHash = $contactListItem->getCombinedContactsHash();
            } elseif ($event instanceof ContactRequestedAgain) {
                $contactListItem = ContactListItem::fromPendingContact(
                    PendingContact::fromContacts(
                        $event->requestedContact()->id(),
                        $event->requesterContact()->id()
                    )
                );
                $combinedContactsHash = $contactListItem->getCombinedContactsHash();
            } else {
                return;
            }

            $existingContactListItem = $this->projectionDocumentManager
                ->createQueryBuilder(ContactListItem::class)
                ->field('id')->equals($combinedContactsHash)
                ->getQuery()
                ->getSingleResult();

            if (
                null !== $existingContactListItem &&
                !($existingContactListItem instanceof ContactListItem)
            ) {
                throw new \Exception();
            }

            if ($existingContactListItem instanceof ContactListItem) {
                $contactListItem = $existingContactListItem->changeFromAnotherContactListItem(
                    $contactListItem
                );
            }

            $this->projectionDocumentManager->persist($contactListItem);
            $this->projectionDocumentManager->flush();
        } catch (\Throwable $exception) {
            throw new ProjectionCannotProcess($exception);
        }
    }
}
