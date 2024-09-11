<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Aggregate;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $contactId = Id::createNew();
        $contactStatus = ActiveContact::fromContacts(
            Id::createNew(),
            Id::createNew()
        );

        $contact = Contact::fromStatus(
            $contactId,
            $contactStatus
        );

        Assert::assertEquals($contactId, $contact->id());
        Assert::assertEquals($contactStatus, $contact->contactStatus());
    }
}
