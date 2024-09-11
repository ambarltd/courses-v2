<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\ValueObject;

use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class InactiveContactTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $firstContact = Id::createNew();
        $secondContact = Id::createNew();

        $inactiveContact = InactiveContact::fromContacts(
            $firstContact,
            $secondContact
        );

        Assert::assertEquals(
            $firstContact,
            $inactiveContact->firstContact()
        );
        Assert::assertEquals(
            $secondContact,
            $inactiveContact->secondContact()
        );
    }
}
