<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestAccepted;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactRequestAcceptedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $accepterContact = Id::createNew();
        $acceptedContact = Id::createNew();

        $contactRequestAccepted = ContactRequestAccepted::fromContacts(
            $aggregateId,
            $authorizerId,
            [1, 2, 3],
            $accepterContact,
            $acceptedContact
        );

        Assert::assertInstanceOf(
            Id::class,
            $contactRequestAccepted->eventId()
        );
        Assert::assertNotContains(
            $contactRequestAccepted->eventId(),
            [
                $contactRequestAccepted->aggregateId(),
                $contactRequestAccepted->authorizerId(),
                $contactRequestAccepted->accepterContact(),
                $contactRequestAccepted->acceptedContact(),
            ]
        );
        Assert::assertEquals(
            $aggregateId,
            $contactRequestAccepted->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $contactRequestAccepted->authorizerId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $contactRequestAccepted->eventMetadata()
        );
        Assert::assertEquals(
            $accepterContact,
            $contactRequestAccepted->accepterContact()
        );
        Assert::assertEquals(
            $acceptedContact,
            $contactRequestAccepted->acceptedContact()
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testTransform(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();

        $contact = Contact::fromStatus(
            $aggregateId,
            PendingContactRequest::fromContacts(
                $requesterContact,
                $requestedContact
            )
        );

        $transformedContact = ContactRequestAccepted::fromContacts(
            $aggregateId,
            $authorizerId,
            [],
            $requestedContact,
            $requesterContact
        )->transformContact($contact);

        if (false === ($transformedContact->contactStatus() instanceof ActiveContact)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $contact->id(),
            $transformedContact->id()
        );
        Assert::assertEquals(
            $requestedContact,
            $transformedContact
                ->contactStatus()
                ->firstContact()
        );
        Assert::assertEquals(
            $requesterContact,
            $transformedContact
                ->contactStatus()
                ->secondContact()
        );
    }
}
