<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\PendingContact;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PendingContactTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testPendingContact(): void
    {
        $contactPair = PendingContact::fromContacts(
            'requested_contact_id',
            'requester_contact_id'
        );

        Assert::assertEquals(
            'requested_contact_id',
            $contactPair->getRequestedContactId()
        );
        Assert::assertEquals(
            'requester_contact_id',
            $contactPair->getRequesterContactId()
        );
    }
}
