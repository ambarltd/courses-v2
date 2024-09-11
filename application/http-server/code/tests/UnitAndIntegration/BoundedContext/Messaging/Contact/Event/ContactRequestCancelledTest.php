<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestCancelled;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactRequestCancelledTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $cancellerContact = Id::createNew();
        $cancelledContact = Id::createNew();

        $contactRequestCancelled = ContactRequestCancelled::fromContacts(
            $aggregateId,
            $authorizerId,
            [1, 2, 3],
            $cancellerContact,
            $cancelledContact
        );

        Assert::assertInstanceOf(
            Id::class,
            $contactRequestCancelled->eventId()
        );
        Assert::assertNotContains(
            $contactRequestCancelled->eventId(),
            [
                $contactRequestCancelled->aggregateId(),
                $contactRequestCancelled->authorizerId(),
                $contactRequestCancelled->cancellerContact(),
                $contactRequestCancelled->cancelledContact(),
            ]
        );
        Assert::assertEquals(
            $aggregateId,
            $contactRequestCancelled->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $contactRequestCancelled->authorizerId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $contactRequestCancelled->eventMetadata()
        );
        Assert::assertEquals(
            $cancellerContact,
            $contactRequestCancelled->cancellerContact()
        );
        Assert::assertEquals(
            $cancelledContact,
            $contactRequestCancelled->cancelledContact()
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
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();
        $authorizerId = Id::createNew();

        $contact = Contact::fromStatus(
            $aggregateId,
            PendingContactRequest::fromContacts(
                $requesterContact,
                $requestedContact
            )
        );

        $transformedContact = ContactRequestCancelled::fromContacts(
            $aggregateId,
            $authorizerId,
            [],
            $requesterContact,
            $requestedContact
        )->transformContact($contact);

        if (false === ($transformedContact->contactStatus() instanceof InactiveContact)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $contact->id(),
            $transformedContact->id()
        );
        Assert::assertEquals(
            $requesterContact,
            $transformedContact->contactStatus()
                ->firstContact()
        );
        Assert::assertEquals(
            $requestedContact,
            $transformedContact->contactStatus()
                ->secondContact()
        );
    }
}
