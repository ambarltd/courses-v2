<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\CancelContactRequest;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class CancelContactRequestHandler
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
     * @var ContactIdFromContacts
     */
    private $contactIdFromContacts;

    public function __construct(
        EventStore $eventStore,
        Queue $queue,
        ContactIdFromContacts $contactIdFromContacts
    ) {
        $this->eventStore = $eventStore;
        $this->queue = $queue;
        $this->contactIdFromContacts = $contactIdFromContacts;
    }

    /**
     * @throws ContactDoesNotExistYet|ContactIsInactive|ContactIsActive
     * @throws InvalidId|CancellerIsNotRequester
     * @throws ProjectionCannotRead|EventStoreCannotWrite|QueuingFailure|EventStoreCannotRead
     */
    public function handle(CancelContactRequest $command): void
    {
        $cancellerContact = $command->authorizerId;

        $contactId = $this
            ->contactIdFromContacts
            ->contactIdFromContacts(
                $cancellerContact,
                $command->cancelledContact
            );

        if (null === $contactId) {
            throw new ContactDoesNotExistYet();
        }

        $this->eventStore->beginTransaction();

        $contact = $this->eventStore->find($contactId);

        if (!$contact instanceof Contact) {
            throw new ContactDoesNotExistYet();
        }

        if ($contact->contactStatus() instanceof InactiveContact) {
            throw new ContactIsInactive();
        }

        if ($contact->contactStatus() instanceof ActiveContact) {
            throw new ContactIsActive();
        }

        if ($cancellerContact !== $contact->contactStatus()->requesterContact()->id()) {
            throw new CancellerIsNotRequester();
        }

        $event = ContactRequestCancelled::fromContacts(
            $contact->id(),
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($cancellerContact),
            Id::fromId($command->cancelledContact)
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }
}
