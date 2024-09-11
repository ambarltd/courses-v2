<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\InactiveContact;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class InactiveContactTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testInactiveContact(): void
    {
        $contactPair = InactiveContact::fromContacts(
            'first_contact_id',
            'second_contact_id'
        );

        Assert::assertEquals(
            'first_contact_id',
            $contactPair->getFirstContactId()
        );
        Assert::assertEquals(
            'second_contact_id',
            $contactPair->getSecondContactId()
        );
    }
}
