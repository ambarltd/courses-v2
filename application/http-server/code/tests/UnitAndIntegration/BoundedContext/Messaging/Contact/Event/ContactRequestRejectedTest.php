<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestRejected;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactRequestRejectedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $rejecterContact = Id::createNew();
        $rejectedContact = Id::createNew();

        $contactRequestRejected = ContactRequestRejected::fromContacts(
            $aggregateId,
            $authorizerId,
            [1, 2, 3],
            $rejecterContact,
            $rejectedContact
        );

        Assert::assertInstanceOf(
            Id::class,
            $contactRequestRejected->eventId()
        );
        Assert::assertNotContains(
            $contactRequestRejected->eventId(),
            [
                $contactRequestRejected->aggregateId(),
                $contactRequestRejected->authorizerId(),
                $contactRequestRejected->rejecterContact(),
                $contactRequestRejected->rejectedContact(),
            ]
        );
        Assert::assertEquals(
            $aggregateId,
            $contactRequestRejected->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $contactRequestRejected->authorizerId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $contactRequestRejected->eventMetadata()
        );
        Assert::assertEquals(
            $rejecterContact,
            $contactRequestRejected->rejecterContact()
        );
        Assert::assertEquals(
            $rejectedContact,
            $contactRequestRejected->rejectedContact()
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

        $transformedContact = ContactRequestRejected::fromContacts(
            $aggregateId,
            $authorizerId,
            [],
            $requestedContact,
            $requesterContact
        )->transformContact($contact);

        if (false === ($transformedContact->contactStatus() instanceof InactiveContact)) {
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
