<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactRequestedAgain;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactRequestedAgainTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();

        $contactRequestedAgain = ContactRequestedAgain::fromContacts(
            $aggregateId,
            $authorizerId,
            [1, 2, 3],
            $requesterContact,
            $requestedContact
        );

        Assert::assertInstanceOf(
            Id::class,
            $contactRequestedAgain->eventId()
        );
        Assert::assertNotContains(
            $contactRequestedAgain->eventId(),
            [
                $aggregateId,
                $authorizerId,
                $requesterContact,
                $requestedContact,
            ]
        );
        Assert::assertEquals(
            $aggregateId,
            $contactRequestedAgain->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $contactRequestedAgain->authorizerId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $contactRequestedAgain->eventMetadata()
        );
        Assert::assertEquals(
            $requesterContact,
            $contactRequestedAgain->requesterContact()
        );
        Assert::assertEquals(
            $requestedContact,
            $contactRequestedAgain->requestedContact()
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
        $firstContact = Id::createNew();
        $secondContact = Id::createNew();

        $contact = Contact::fromStatus(
            $aggregateId,
            InactiveContact::fromContacts(
                $firstContact,
                $secondContact
            )
        );

        $transformedContact = ContactRequestedAgain::fromContacts(
            $aggregateId,
            $authorizerId,
            [],
            $firstContact,
            $secondContact
        )->transformContact($contact);

        if (false === ($transformedContact->contactStatus() instanceof PendingContactRequest)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $contact->id(),
            $transformedContact->id()
        );
        Assert::assertEquals(
            $firstContact,
            $transformedContact
                ->contactStatus()
                ->requesterContact()
        );
        Assert::assertEquals(
            $secondContact,
            $transformedContact
                ->contactStatus()
                ->requestedContact()
        );
    }
}
