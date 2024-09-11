<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\Event;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class OneToOneConversationStartedTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testCreate(): void
    {
        $authorizerId = Id::createNew();
        $sender = Id::createNew();
        $recipient = Id::createNew();
        $expirationDate = new \DateTimeImmutable();
        $maxNumberOfViews = 37;

        $event = OneToOneConversationStarted::fromProperties(
            $authorizerId,
            [1, 2, 4],
            $sender,
            $recipient,
            $expirationDate,
            $maxNumberOfViews
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
        Assert::assertEquals(
            $sender,
            $event->sender()
        );
        Assert::assertEquals(
            $recipient,
            $event->recipient()
        );
        Assert::assertEquals(
            $expirationDate,
            $event->expirationDate()
        );
        Assert::assertEquals(
            $maxNumberOfViews,
            $event->maxNumberOfViews()
        );
    }

    /**
     * @test
     */
    public function testCreateAggregate(): void
    {
        $authorizerId = Id::createNew();
        $sender = Id::createNew();
        $recipient = Id::createNew();
        $expirationDate = new \DateTimeImmutable();
        $maxNumberOfViews = 37;

        $event = OneToOneConversationStarted::fromProperties(
            $authorizerId,
            [1, 2, 4],
            $sender,
            $recipient,
            $expirationDate,
            $maxNumberOfViews
        );

        $aggregate = $event->createOneToOneConversation();

        Assert::assertInstanceOf(
            Id::class,
            $aggregate->id()
        );
        Assert::assertEquals(
            $event->sender(),
            $aggregate->sender()
        );
        Assert::assertEquals(
            $event->recipient(),
            $aggregate->recipient()
        );
        Assert::assertEquals(
            $event->maxNumberOfViews(),
            $aggregate->maxNumberOfViews()
        );
        Assert::assertEquals(
            $event->expirationDate(),
            $aggregate->expirationDate()
        );
        Assert::assertEquals(
            PushStatus::pushed(),
            $aggregate->pushStatus()
        );
    }
}
