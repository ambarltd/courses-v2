<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\Event;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationDeletedBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class OneToOneConversationDeletedBySenderTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $aggregateId = Id::createNew();
        $authorizerId = Id::createNew();

        $event = OneToOneConversationDeletedBySender::fromProperties(
            $aggregateId,
            $authorizerId,
            [1, 2, 4]
        );

        Assert::assertInstanceOf(
            Id::class,
            $event->eventId()
        );
        Assert::assertInstanceOf(
            Id::class,
            $event->aggregateId()
        );
        Assert::assertNotEquals(
            $event->eventId(),
            $event->aggregateId()
        );
        Assert::assertInstanceOf(
            Id::class,
            $event->authorizerId()
        );
        Assert::assertEquals(
            $authorizerId,
            $event->authorizerId()
        );
        Assert::assertEquals(
            null,
            $event->sourceEventId()
        );
        Assert::assertInstanceOf(
            \DateTimeImmutable::class,
            $event->eventOccurredOn()
        );
        Assert::assertEquals(
            [1, 2, 4],
            $event->eventMetadata()
        );
    }

    /**
     * @test
     */
    public function testTransformAggregate(): void
    {
        $conversationId = Id::createNew();
        $authorizerId = Id::createNew();
        $sender = Id::createNew();
        $recipient = Id::createNew();
        $expirationDate = new \DateTimeImmutable();
        $maxNumberOfViews = 37;

        $oneToOneConversation = OneToOneConversation::fromProperties(
            $conversationId,
            $sender,
            $recipient,
            $maxNumberOfViews,
            $expirationDate,
            PushStatus::pushed()
        );

        $event = OneToOneConversationDeletedBySender::fromProperties(
            $conversationId,
            $authorizerId,
            []
        );

        $aggregate = $event->transformOneToOneConversation($oneToOneConversation);

        Assert::assertInstanceOf(
            Id::class,
            $aggregate->id()
        );
        Assert::assertEquals(
            $sender,
            $aggregate->sender()
        );
        Assert::assertEquals(
            $recipient,
            $aggregate->recipient()
        );
        Assert::assertEquals(
            $maxNumberOfViews,
            $aggregate->maxNumberOfViews()
        );
        Assert::assertEquals(
            $expirationDate,
            $aggregate->expirationDate()
        );
        Assert::assertEquals(
            PushStatus::deletedBySender(),
            $aggregate->pushStatus()
        );
    }
}
