<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\RejectContactRequest;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
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

class RejectContactRequestHandler
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
     * @throws ContactDoesNotExistYet|ContactIsInactive|ContactIsActive|RejecterIsNotRequested
     * @throws ProjectionCannotRead|InvalidId|EventStoreCannotWrite|QueuingFailure|EventStoreCannotRead
     */
    public function handle(RejectContactRequest $command): void
    {
        $rejecterContact = $command->authorizerId;

        $contactId = $this
            ->contactIdFromContacts
            ->contactIdFromContacts(
                $rejecterContact,
                $command->rejectedContact
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

        if ($rejecterContact !== $contact->contactStatus()->requestedContact()->id()) {
            throw new RejecterIsNotRequested();
        }

        $event = ContactRequestRejected::fromContacts(
            $contact->id(),
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($rejecterContact),
            Id::fromId($command->rejectedContact)
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }
}
