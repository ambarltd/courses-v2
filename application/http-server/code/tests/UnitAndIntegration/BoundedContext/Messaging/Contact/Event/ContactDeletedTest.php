<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\Event\ContactDeleted;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactDeletedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $deleterContact = Id::createNew();
        $deletedContact = Id::createNew();

        $contactDeleted = ContactDeleted::fromContacts(
            $aggregateId,
            $authorizerId,
            [1, 2, 3],
            $deleterContact,
            $deletedContact
        );

        Assert::assertInstanceOf(
            Id::class,
            $contactDeleted->eventId()
        );
        Assert::assertNotContains(
            $contactDeleted->eventId(),
            [
                $contactDeleted->aggregateId(),
                $contactDeleted->authorizerId(),
                $contactDeleted->deleterContact(),
                $contactDeleted->deletedContact(),
            ]
        );
        Assert::assertEquals(
            $aggregateId,
            $contactDeleted->aggregateId()
        );
        Assert::assertEquals(
            $authorizerId,
            $contactDeleted->authorizerId()
        );
        Assert::assertEquals(
            null,
            $contactDeleted->sourceEventId()
        );
        Assert::assertEquals(
            [1, 2, 3],
            $contactDeleted->eventMetadata()
        );
        Assert::assertEquals(
            $deleterContact,
            $contactDeleted->deleterContact()
        );
        Assert::assertEquals(
            $deletedContact,
            $contactDeleted->deletedContact()
        );
    }

    /**
     * @test
     *
     * @throws \Exception
     */
    public function testTransformActive(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();
        $firstContact = Id::createNew();
        $secondContact = Id::createNew();

        $contact = Contact::fromStatus(
            $aggregateId,
            ActiveContact::fromContacts(
                $firstContact,
                $secondContact
            )
        );

        $transformedContact = ContactDeleted::fromContacts(
            $aggregateId,
            $authorizerId,
            [],
            $firstContact,
            $secondContact
        )->transformContact($contact);

        Assert::assertEquals(
            $contact->id(),
            $transformedContact->id()
        );

        if (false === ($transformedContact->contactStatus() instanceof InactiveContact)) {
            throw new \Exception();
        }

        Assert::assertEquals(
            $firstContact,
            $transformedContact
                ->contactStatus()
                ->firstContact()
        );
        Assert::assertEquals(
            $secondContact,
            $transformedContact
                ->contactStatus()
                ->secondContact()
        );
    }
}
