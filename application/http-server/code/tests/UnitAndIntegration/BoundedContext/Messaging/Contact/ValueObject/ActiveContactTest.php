<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\ValueObject;

use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ActiveContactTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $firstContact = Id::createNew();
        $secondContact = Id::createNew();

        $activeContact = ActiveContact::fromContacts(
            $firstContact,
            $secondContact
        );

        Assert::assertEquals(
            $firstContact,
            $activeContact->firstContact()
        );
        Assert::assertEquals(
            $secondContact,
            $activeContact->secondContact()
        );
    }
}
