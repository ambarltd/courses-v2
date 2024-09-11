<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\Contact\Command\RejectContactRequest;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\ContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\RejectContactRequestHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class RejectContactRequestHandlerTest extends HandlerTestBase
{
    /**
     * @test
     */
    public function testHandle(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $contactRequested = ContactRequested::fromContacts(
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($contactRequested);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RejectContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                function (string $firstContact, string $secondContact) use ($contactRequested): ?string {
                    if (
                        $firstContact === $contactRequested->requesterContact()->id() &&
                        $secondContact === $contactRequested->requestedContact()->id()
                    ) {
                        return $contactRequested->aggregateId()->id();
                    }
                    if (
                        $firstContact === $contactRequested->requestedContact()->id() &&
                        $secondContact === $contactRequested->requesterContact()->id()
                    ) {
                        return $contactRequested->aggregateId()->id();
                    }

                    return null;
                }
            )
        );

        $command = new RejectContactRequest();
        $command->authorizerId = $requestedContact->id();
        $command->rejectedContact = $requesterContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (!($storedEvent instanceof ContactRequestRejected)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );
        Assert::assertEquals(
            $contactRequested->aggregateId()->id(),
            $storedEvent->aggregateId()->id()
        );
        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->authorizerId()->id()
        );
        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );
        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->rejecterContact()->id()
        );
        Assert::assertEquals(
            $command->rejectedContact,
            $storedEvent->rejectedContact()->id()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\ContactDoesNotExistYet
     */
    public function testContactDoesNotExistYet(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $contactRequested = ContactRequested::fromContacts(
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($contactRequested);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RejectContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                null
            )
        );

        $command = new RejectContactRequest();
        $command->authorizerId = $requestedContact->id();
        $command->rejectedContact = $requesterContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\ContactIsInactive
     */
    public function testContactIsInactive(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $contactRequested = ContactRequested::fromContacts(
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );
        $contactRequestAccepted = ContactRequestAccepted::fromContacts(
            $contactRequested->aggregateId(),
            $requestedContact,
            $this->mockMetadata(),
            $requestedContact,
            $requesterContact
        );
        $contactDeleted = ContactDeleted::fromContacts(
            $contactRequested->aggregateId(),
            $requesterContact,
            $this->mockMetadata(),
            $requestedContact,
            $requestedContact
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($contactRequested);
        $this->getInMemoryEventStore()->save($contactRequestAccepted);
        $this->getInMemoryEventStore()->save($contactDeleted);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RejectContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new RejectContactRequest();
        $command->authorizerId = $requestedContact->id();
        $command->rejectedContact = $requesterContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\ContactIsActive
     */
    public function testContactIsActive(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $contactRequested = ContactRequested::fromContacts(
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );
        $contactRequestAccepted = ContactRequestAccepted::fromContacts(
            $contactRequested->aggregateId(),
            $requestedContact,
            $this->mockMetadata(),
            $requestedContact,
            $requesterContact
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($contactRequested);
        $this->getInMemoryEventStore()->save($contactRequestAccepted);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RejectContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new RejectContactRequest();
        $command->authorizerId = $requestedContact->id();
        $command->rejectedContact = $requesterContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RejectContactRequest\RejecterIsNotRequested
     */
    public function testRejecterIsNotRequested(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $contactRequested = ContactRequested::fromContacts(
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($contactRequested);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new RejectContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new RejectContactRequest();
        $command->authorizerId = $requesterContact->id();
        $command->rejectedContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}
