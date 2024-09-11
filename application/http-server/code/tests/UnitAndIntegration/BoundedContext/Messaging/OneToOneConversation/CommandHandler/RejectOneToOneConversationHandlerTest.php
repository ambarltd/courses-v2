<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\RejectOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\RejectOneToOneConversation\RejectOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationPulledBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationRejectedByRecipient;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class RejectOneToOneConversationHandlerTest extends HandlerTestBase
{
    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\RejectOneToOneConversation\ConversationDoesNotExist
     */
    public function testConversationDoesNotExist(): void
    {
        $command = new RejectOneToOneConversation();
        $command->authorizerId = Id::createNew()->id();
        $command->conversationId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new RejectOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\RejectOneToOneConversation\OnlyRecipientCanReject
     */
    public function testOnlyRecipientCanReject(): void
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

        $command = new RejectOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new RejectOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\RejectOneToOneConversation\ConversationIsExpired
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

        $command = new RejectOneToOneConversation();
        $command->authorizerId = $recipient->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new RejectOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\RejectOneToOneConversation\CannotRejectAnymore
     */
    public function testCannotRejectAnymore(): void
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

        $command = new RejectOneToOneConversation();
        $command->authorizerId = $recipient->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new RejectOneToOneConversationHandler(
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

        $command = new RejectOneToOneConversation();
        $command->authorizerId = $recipient->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new RejectOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);

        /** @var OneToOneConversationRejectedByRecipient $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        /** @var OneToOneConversationRejectedByRecipient $queuedEvent */
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
