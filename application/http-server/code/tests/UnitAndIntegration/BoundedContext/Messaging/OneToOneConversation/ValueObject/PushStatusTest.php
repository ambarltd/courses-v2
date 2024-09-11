<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\ValueObject;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class PushStatusTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $pushStatus = PushStatus::pushed();
        Assert::assertTrue($pushStatus->isPushed());
        Assert::assertFalse($pushStatus->isPulledBySender());
        Assert::assertFalse($pushStatus->isDeletedBySender());
        Assert::assertFalse($pushStatus->isRejectedByRecipient());

        $pushStatus = PushStatus::pulledBySender();
        Assert::assertFalse($pushStatus->isPushed());
        Assert::assertTrue($pushStatus->isPulledBySender());
        Assert::assertFalse($pushStatus->isDeletedBySender());
        Assert::assertFalse($pushStatus->isRejectedByRecipient());

        $pushStatus = PushStatus::deletedBySender();
        Assert::assertFalse($pushStatus->isPushed());
        Assert::assertFalse($pushStatus->isPulledBySender());
        Assert::assertTrue($pushStatus->isDeletedBySender());
        Assert::assertFalse($pushStatus->isRejectedByRecipient());

        $pushStatus = PushStatus::rejectedByRecipient();
        Assert::assertFalse($pushStatus->isPushed());
        Assert::assertFalse($pushStatus->isPulledBySender());
        Assert::assertFalse($pushStatus->isDeletedBySender());
        Assert::assertTrue($pushStatus->isRejectedByRecipient());
    }
}
