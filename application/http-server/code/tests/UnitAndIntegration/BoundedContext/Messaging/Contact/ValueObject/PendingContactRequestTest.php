<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\Contact\ValueObject;

use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PendingContactRequestTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $requesterContact = Id::createNew();
        $requestedContact = Id::createNew();

        $pendingContactRequest = PendingContactRequest::fromContacts(
            $requesterContact,
            $requestedContact
        );

        Assert::assertEquals(
            $requesterContact,
            $pendingContactRequest->requesterContact()
        );
        Assert::assertEquals(
            $requestedContact,
            $pendingContactRequest->requestedContact()
        );
    }
}
