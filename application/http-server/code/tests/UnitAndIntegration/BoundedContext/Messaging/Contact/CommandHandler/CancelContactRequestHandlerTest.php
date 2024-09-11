<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\Contact\Command\CancelContactRequest;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\CancelContactRequestHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\ContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class CancelContactRequestHandlerTest extends HandlerTestBase
{
    /**
     * @test
     *
     * @throws \Exception
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

        $handler = new CancelContactRequestHandler(
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

        $command = new CancelContactRequest();
        $command->authorizerId = $requesterContact->id();
        $command->cancelledContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (!($storedEvent instanceof ContactRequestCancelled)) {
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
            $storedEvent->cancellerContact()->id()
        );
        Assert::assertEquals(
            $command->cancelledContact,
            $storedEvent->cancelledContact()->id()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\ContactDoesNotExistYet
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

        $handler = new CancelContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                null
            )
        );

        $command = new CancelContactRequest();
        $command->authorizerId = $requesterContact->id();
        $command->cancelledContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\ContactIsInactive
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
        $contactRequestCancelled = ContactRequestCancelled::fromContacts(
            $contactRequested->aggregateId(),
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($contactRequested);
        $this->getInMemoryEventStore()->save($contactRequestCancelled);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new CancelContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new CancelContactRequest();
        $command->authorizerId = $requesterContact->id();
        $command->cancelledContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\ContactIsActive
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

        $handler = new CancelContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new CancelContactRequest();
        $command->authorizerId = $requesterContact->id();
        $command->cancelledContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\CancelContactRequest\CancellerIsNotRequester
     */
    public function testCancellerIsNotRequester(): void
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

        $handler = new CancelContactRequestHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new CancelContactRequest();
        $command->authorizerId = $requestedContact->id();
        $command->cancelledContact = $requesterContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}
