<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\PullOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\PullOneToOneConversation\PullOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationPulledBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class PullOneToOneConversationHandlerTest extends HandlerTestBase
{
    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\PullOneToOneConversation\ConversationDoesNotExist
     */
    public function testConversationDoesNotExist(): void
    {
        $command = new PullOneToOneConversation();
        $command->authorizerId = Id::createNew()->id();
        $command->conversationId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new PullOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\PullOneToOneConversation\OnlySenderCanPull
     */
    public function testOnlySenderCanPull(): void
    {
        $sender = Id::createNew();
        $recipient = Id::createNew();

        $conversationStarted = OneToOneConversationStarted::fromProperties(
            $sender,
            $this->mockMetadata(),
            $sender,
            $recipient,
            new \DateTimeImmutable('+1 minute'),
            91
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($conversationStarted);
        $this->getInMemoryEventStore()->completeTransaction();

        $command = new PullOneToOneConversation();
        $command->authorizerId = $recipient->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new PullOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\PullOneToOneConversation\ConversationIsExpired
     */
    public function testConversationIsExpired(): void
    {
        $sender = Id::createNew();
        $recipient = Id::createNew();

        $conversationStarted = OneToOneConversationStarted::fromProperties(
            $sender,
            $this->mockMetadata(),
            $sender,
            $recipient,
            new \DateTimeImmutable('-1 second'),
            91
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($conversationStarted);
        $this->getInMemoryEventStore()->completeTransaction();

        $command = new PullOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new PullOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\PullOneToOneConversation\CannotPullAnymore
     */
    public function testCannotPullAnymore(): void
    {
        $sender = Id::createNew();
        $recipient = Id::createNew();

        $conversationStarted = OneToOneConversationStarted::fromProperties(
            $sender,
            $this->mockMetadata(),
            $sender,
            $recipient,
            new \DateTimeImmutable('+1 minute'),
            91
        );

        $conversationPulled = OneToOneConversationPulledBySender::fromProperties(
            $conversationStarted->aggregateId(),
            $sender,
            $this->mockMetadata()
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($conversationStarted);
        $this->getInMemoryEventStore()->save($conversationPulled);
        $this->getInMemoryEventStore()->completeTransaction();

        $command = new PullOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new PullOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @test
     */
    public function testHandle(): void
    {
        $sender = Id::createNew();
        $recipient = Id::createNew();

        $conversationStarted = OneToOneConversationStarted::fromProperties(
            $sender,
            $this->mockMetadata(),
            $sender,
            $recipient,
            new \DateTimeImmutable('+1 minute'),
            91
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($conversationStarted);
        $this->getInMemoryEventStore()->completeTransaction();
        $this->getInMemoryQueue()->enqueue($conversationStarted);

        $command = new PullOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new PullOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);

        /** @var OneToOneConversationPulledBySender $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        /** @var OneToOneConversationPulledBySender $queuedEvent */
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[1];

        Assert::assertEquals(
            $storedEvent,
            $queuedEvent
        );

        Assert::assertEquals(
            $command->authorizerId,
            $storedEvent->authorizerId()->id()
        );

        Assert::assertEquals(
            $command->metadata,
            $storedEvent->eventMetadata()
        );

        Assert::assertEquals(
            null,
            $storedEvent->sourceEventId()
        );

        Assert::assertInstanceOf(
            \DateTimeImmutable::class,
            $storedEvent->eventOccurredOn()
        );

        Assert::assertEquals(
            $conversationStarted->aggregateId(),
            $storedEvent->aggregateId()
        );
    }
}
