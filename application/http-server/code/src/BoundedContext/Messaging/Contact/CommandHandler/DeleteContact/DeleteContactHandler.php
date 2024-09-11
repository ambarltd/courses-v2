<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Command\DeleteContact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotRead;
use Galeas\Api\Common\ExceptionBase\EventStoreCannotWrite;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotRead;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Common\Id\InvalidId;
use Galeas\Api\Service\EventStore\EventStore;
use Galeas\Api\Service\Queue\Queue;

class DeleteContactHandler
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
     * @throws ContactDoesNotExistYet|ContactIsInactive|ContactIsPending
     * @throws InvalidId|ProjectionCannotRead|EventStoreCannotRead|EventStoreCannotWrite|QueuingFailure
     */
    public function handle(DeleteContact $command): void
    {
        $deleterContact = $command->authorizerId;

        $contactId = $this
            ->contactIdFromContacts
            ->contactIdFromContacts(
                $deleterContact,
                $command->deletedContact
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
        if ($contact->contactStatus() instanceof PendingContactRequest) {
            throw new ContactIsPending();
        }

        $event = ContactDeleted::fromContacts(
            $contact->id(),
            Id::fromId($command->authorizerId),
            $command->metadata,
            Id::fromId($deleterContact),
            Id::fromId($command->deletedContact)
        );

        $this->eventStore->save($event);
        $this->eventStore->completeTransaction();

        $this->queue->enqueue($event);
    }
}
