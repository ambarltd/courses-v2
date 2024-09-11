<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\RequestContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class RequestContactHandler
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
     * @var DoesContactExist
     */
    private $doesContactExist;

    /**
     * @var DoesRequestedContactExist
     */
    private $doesRequestedContactExist;

    /**
     * @var ContactIdFromContacts
     */
    private $contactIdFromContacts;

    public function __construct(
        EventStore $eventStore,
        Queue $queue,
        DoesContactExist $doesContactExist,
        DoesRequestedContactExist $doesRequestedContactExist,
        ContactIdFromContacts $contactIdFromContacts
    ) {
        $this->eventStore = $eventStore;
        $this->queue = $queue;
        $this->doesContactExist = $doesContactExist;
        $this->doesRequestedContactExist = $doesRequestedContactExist;
        $this->contactIdFromContacts = $contactIdFromContacts;
    }

    /**
     * @throws CannotRequestSelf|RequestedContactDoesNotExist
     * @throws ContactDoesNotExistYet|ContactIsActive|ContactIsPending
     * @throws InvalidId|ProjectionCannotRead|InvalidId|EventStoreCannotWrite|EventStoreCannotRead|QueuingFailure
     */
    public function handle(RequestContact $command): void
    {
        $requesterContact = $command->authorizerId;

        if ($requesterContact === $command->requestedContact) {
            throw new CannotRequestSelf();
        }

        if (false === $this->doesContactExist->doesContactExist($requesterContact, $command->requestedContact)) {
            $this->requestContact($command);
        } else {
            $this->requestContactAgain($command);
        }
    }

    /**
     * @throws ProjectionCannotRead|RequestedContactDoesNotExist|InvalidId
     * @throws EventStoreCannotWrite|QueuingFailure
     */
    private function requestContact(RequestContact $command): void
    {
        $requesterContact = $command->authorizerId;

        if (false === $this->doesRequestedContactExist->doesRequestedContactExist($command->requestedContact)) {
            throw new RequestedContactDoesNotExist();
        }

        $event = ContactRequested::fromContacts(
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($requesterContact),
            Id::fromId($command->requestedContact)
        );

        $this->eventStore->beginTransaction();
        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }

    /**
     * @throws ContactDoesNotExistYet|ContactIsActive|ContactIsPending
     * @throws InvalidId|ProjectionCannotRead|InvalidId|EventStoreCannotWrite|EventStoreCannotRead|QueuingFailure
     */
    public function requestContactAgain(RequestContact $command): void
    {
        $requesterContact = $command->authorizerId;

        $contactId = $this
            ->contactIdFromContacts
            ->contactIdFromContacts(
                $requesterContact,
                $command->requestedContact
            );

        if (null === $contactId) {
            throw new ContactDoesNotExistYet();
        }

        $this->eventStore->beginTransaction();

        $contact = $this->eventStore->find($contactId);

        if (!$contact instanceof Contact) {
            throw new ContactDoesNotExistYet();
        }

        if ($contact->contactStatus() instanceof ActiveContact) {
            throw new ContactIsActive();
        }
        if ($contact->contactStatus() instanceof PendingContactRequest) {
            throw new ContactIsPending();
        }

        $event = ContactRequestedAgain::fromContacts(
            $contact->id(),
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($requesterContact),
            Id::fromId($command->requestedContact)
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }
}
