<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactList;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactList\ContactDetails;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactDetailsTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testContactDetails(): void
    {
        $contactDetails = ContactDetails::fromUserIdAndUsername(
            'contact_id',
            'contact_username'
        );

        Assert::assertEquals(
            'contact_id',
            $contactDetails->getUserId()
        );
        Assert::assertEquals(
            'contact_username',
            $contactDetails->getUsername()
        );

        $contactDetails->changeUsername('contact_username_xyz');

        Assert::assertEquals(
            'contact_id',
            $contactDetails->getUserId()
        );
        Assert::assertEquals(
            'contact_username_xyz',
            $contactDetails->getUsername()
        );
    }
}
