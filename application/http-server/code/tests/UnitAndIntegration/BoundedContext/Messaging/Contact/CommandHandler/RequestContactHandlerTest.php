<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\Contact\Command\RequestContact;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\ContactIdFromContacts;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\DoesContactExist;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\DoesRequestedContactExist;
use Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\RequestContactHandler;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\ValidIds;

class RequestContactHandlerTest extends HandlerTestBase
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

        $handler = new RequestContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                DoesContactExist::class,
                'doesContactExist',
                function (string $firstContact, string $secondContact): bool {
                    if (
                        $firstContact === ValidIds::listValidIds()[0] &&
                        $secondContact === ValidIds::listValidIds()[1]
                    ) {
                        return false;
                    }
                    if (
                        $firstContact === ValidIds::listValidIds()[1] &&
                        $secondContact === ValidIds::listValidIds()[0]
                    ) {
                        return false;
                    }

                    return true;
                }
            ),
            $this->mockForCommandHandlerWithCallback(
                DoesRequestedContactExist::class,
                'doesRequestedContactExist',
                function (string $requestedContact): bool {
                    if ($requestedContact === ValidIds::listValidIds()[1]) {
                        return true;
                    }

                    return false;
                }
            ),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                null
            )
        );

        $command = new RequestContact();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->requestedContact = ValidIds::listValidIds()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[0];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];
        if (!($storedEvent instanceof ContactRequested)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
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
            $storedEvent->requesterContact()->id()
        );
        Assert::assertEquals(
            $command->requestedContact,
            $storedEvent->requestedContact()->id()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\CannotRequestSelf
     */
    public function testCannotRequestSelf(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $contactRequested = ContactRequested::fromContacts(
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );

        $handler = new RequestContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesContactExist::class,
                'doesContactExist',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                DoesRequestedContactExist::class,
                'doesRequestedContactExist',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
               null
            )
        );

        $command = new RequestContact();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->requestedContact = ValidIds::listValidIds()[0];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\RequestedContactDoesNotExist
     */
    public function testRequestedContactDoesNotExist(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $contactRequested = ContactRequested::fromContacts(
            $requesterContact,
            $this->mockMetadata(),
            $requesterContact,
            $requestedContact
        );

        $handler = new RequestContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesContactExist::class,
                'doesContactExist',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                DoesRequestedContactExist::class,
                'doesRequestedContactExist',
                false
            ),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                null
            )
        );

        $command = new RequestContact();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->requestedContact = ValidIds::listValidIds()[1];
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testContactAlreadyExists(): void
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

        $handler = new RequestContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesContactExist::class,
                'doesContactExist',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                DoesRequestedContactExist::class,
                'doesRequestedContactExist',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new RequestContact();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->requestedContact = ValidIds::listValidIds()[1];
        $command->metadata = $this->mockMetadata();
        $handler->handle($command);

        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[3];
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

        if (!($storedEvent instanceof ContactRequestedAgain)) {
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
            $storedEvent->requesterContact()->id()
        );
        Assert::assertEquals(
            $command->requestedContact,
            $storedEvent->requestedContact()->id()
        );
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\ContactIsActive
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

        $handler = new RequestContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesContactExist::class,
                'doesContactExist',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                DoesRequestedContactExist::class,
                'doesRequestedContactExist',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new RequestContact();
        $command->authorizerId = $requesterContact->id();
        $command->requestedContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\Contact\CommandHandler\RequestContact\ContactIsPending
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

        $handler = new RequestContactHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                DoesContactExist::class,
                'doesContactExist',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                DoesRequestedContactExist::class,
                'doesRequestedContactExist',
                true
            ),
            $this->mockForCommandHandlerWithReturnValue(
                ContactIdFromContacts::class,
                'contactIdFromContacts',
                $contactRequested->aggregateId()->id()
            )
        );

        $command = new RequestContact();
        $command->authorizerId = $requesterContact->id();
        $command->requestedContact = $requestedContact->id();
        $command->metadata = $this->mockMetadata();

        $handler->handle($command);
    }
}
