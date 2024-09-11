<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequested;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactRequestedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $authorizerId = Id::createNew();
        $requester = Id::createNew();
        $requested = Id::createNew();

        $contactRequested = ContactRequested::fromContacts(
            $authorizerId,
            [1, 2, 3],
            $requester,
            $requested
        );

        Assert::assertInstanceOf(
            Id::class,
            $contactRequested->eventId()
        );
        Assert::assertInstanceOf(
            Id::class,
            $contactRequested->aggregateId()
        );
        Assert::assertNotEquals(
            $contactRequested->eventId(),
            $contactRequested->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $contactRequested->authorizerId()
        );
        Assert::assertEquals(
            null,
            $contactRequested->sourceEventId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $contactRequested->eventMetadata()
        );
        Assert::assertEquals(
            $requester,
            $contactRequested->requesterContact()
        );
        Assert::assertEquals(
            $requested,
            $contactRequested->requestedContact()
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testCreateAggregate(): void
    {
        $authorizerId = Id::createNew();
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();

        $contact = ContactRequested::fromContacts(
            $authorizerId,
            [],
            $requesterContact,
            $requestedContact
        )->createContact();

        if (false === ($contact->contactStatus() instanceof PendingContactRequest)) {
            throw new \Exception();
        }

        Assert::assertInstanceOf(
            Id::class,
            $contact->id()
        );
        Assert::assertEquals(
            $requesterContact,
            $contact
                ->contactStatus()
                ->requesterContact()
        );
        Assert::assertEquals(
            $requestedContact,
            $contact
                ->contactStatus()
                ->requestedContact()
        );
    }
}
