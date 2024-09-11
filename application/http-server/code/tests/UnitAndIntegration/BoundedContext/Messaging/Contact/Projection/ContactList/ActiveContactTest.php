<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ActiveContact;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ActiveContactTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testActiveContact(): void
    {
        $contactPair = ActiveContact::fromContacts(
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
