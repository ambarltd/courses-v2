<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\Aggregate;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class OneToOneConversationTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $conversationId = Id::createNew();
        $sender = Id::createNew();
        $recipient = Id::createNew();
        $maxNumberOfViews = 52;
        $expirationDate = new \DateTimeImmutable('now');
        $pushStatus = PushStatus::pushed();

        $oneToOneConversation = OneToOneConversation::fromProperties(
            $conversationId,
            $sender,
            $recipient,
            $maxNumberOfViews,
            $expirationDate,
            $pushStatus
        );

        Assert::assertInstanceOf(OneToOneConversation::class, $oneToOneConversation);
        Assert::assertEquals($conversationId, $oneToOneConversation->id());
        Assert::assertEquals($sender, $oneToOneConversation->sender());
        Assert::assertEquals($recipient, $oneToOneConversation->recipient());
        Assert::assertEquals($maxNumberOfViews, $oneToOneConversation->maxNumberOfViews());
        Assert::assertEquals($expirationDate, $oneToOneConversation->expirationDate());
        Assert::assertEquals($pushStatus, $oneToOneConversation->pushStatus());
    }
}
