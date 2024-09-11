<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\Contact\Command\DeleteContact;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact\ContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact\DeleteContactHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class DeleteContactHandlerTest extends HandlerTestBase
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

        $handler = new DeleteContactHandler(
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

        $command = new DeleteContact();
        $command->authorizerId = $requesterContact->id();
        $command->deletedContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[2];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (!($storedEvent instanceof ContactDeleted)) {
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
            $storedEvent->deleterContact()->id()
        );
        Assert::assertEquals(
            $command->deletedContact,
            $storedEvent->deletedContact()->id()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact\ContactDoesNotExistYet
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

        $handler = new DeleteContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                null
            )
        );

        $command = new DeleteContact();
        $command->authorizerId = $requesterContact->id();
        $command->deletedContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact\ContactIsInactive
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
            $requesterContact,
            $requestedContact
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($contactRequested);
        $this->getInMemoryEventStore()->save($contactRequestAccepted);
        $this->getInMemoryEventStore()->save($contactDeleted);
        $this->getInMemoryEventStore()->completeTransaction();

        $handler = new DeleteContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new DeleteContact();
        $command->authorizerId = $requesterContact->id();
        $command->deletedContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\DeleteContact\ContactIsPending
     */
    public function testContactIsPending(): void
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

        $handler = new DeleteContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new DeleteContact();
        $command->authorizerId = $requesterContact->id();
        $command->deletedContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}
