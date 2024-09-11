<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\Projection\ContactPair;

use Galeas\Api\BoundedContext\Messaging\Contact\Projection\ContactPair\ContactPair;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ContactPairTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testContactPair(): void
    {
        $contactPair = ContactPair::fromProperties(
            'contact_id',
            'first_contact_id',
            'second_contact_id'
        );

        Assert::assertEquals(
            'contact_id',
            $contactPair->getContactId()
        );
        Assert::assertEquals(
            'first_contact_id',
            $contactPair->getFirstContactId()
        );
        Assert::assertEquals(
            'second_contact_id',
            $contactPair->getSecondContactId()
        );

        $contactPair->changeProperties(
            'contact_id_2',
            'first_contact_id_2',
            'second_contact_id_2'
        );

        Assert::assertEquals(
            'contact_id_2',
            $contactPair->getContactId()
        );
        Assert::assertEquals(
            'first_contact_id_2',
            $contactPair->getFirstContactId()
        );
        Assert::assertEquals(
            'second_contact_id_2',
            $contactPair->getSecondContactId()
        );
    }
}
