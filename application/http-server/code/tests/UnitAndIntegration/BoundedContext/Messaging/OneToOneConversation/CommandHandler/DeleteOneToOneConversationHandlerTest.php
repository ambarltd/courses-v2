<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\DeleteOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\DeleteOneToOneConversation\DeleteOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationDeletedBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationPulledBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;

class DeleteOneToOneConversationHandlerTest extends HandlerTestBase
{
    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\DeleteOneToOneConversation\ConversationDoesNotExist
     */
    public function testConversationDoesNotExist(): void
    {
        $command = new DeleteOneToOneConversation();
        $command->authorizerId = Id::createNew()->id();
        $command->conversationId = Id::createNew()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\DeleteOneToOneConversation\OnlySenderCanDelete
     */
    public function testOnlySenderCanDelete(): void
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

        $command = new DeleteOneToOneConversation();
        $command->authorizerId = $recipient->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\DeleteOneToOneConversation\CannotDeleteAnymore
     */
    public function testCannotDeleteAnymore(): void
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
        $conversationDeleted = OneToOneConversationDeletedBySender::fromProperties(
            $conversationStarted->aggregateId(),
            $sender,
            $this->mockMetadata()
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($conversationStarted);
        $this->getInMemoryEventStore()->save($conversationDeleted);
        $this->getInMemoryEventStore()->completeTransaction();

        $command = new DeleteOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);
    }

    /**
     * @test
     */
    public function testHandlePulled(): void
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
        $this->getInMemoryQueue()->enqueue($conversationStarted);
        $this->getInMemoryQueue()->enqueue($conversationPulled);

        $command = new DeleteOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);

        /** @var OneToOneConversationDeletedBySender $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[2];
        /** @var OneToOneConversationDeletedBySender $queuedEvent */
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[2];

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

    /**
     * @test
     */
    public function testHandlePushed(): void
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

        $command = new DeleteOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);

        /** @var OneToOneConversationDeletedBySender $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        /** @var OneToOneConversationDeletedBySender $queuedEvent */
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

    /**
     * @test
     */
    public function testHandlePushedAndExpired(): void
    {
        $sender = Id::createNew();
        $recipient = Id::createNew();

        $conversationStarted = OneToOneConversationStarted::fromProperties(
            $sender,
            $this->mockMetadata(),
            $sender,
            $recipient,
            new \DateTimeImmutable('-1 minute'),
            91
        );

        $this->getInMemoryEventStore()->beginTransaction();
        $this->getInMemoryEventStore()->save($conversationStarted);
        $this->getInMemoryEventStore()->completeTransaction();
        $this->getInMemoryQueue()->enqueue($conversationStarted);

        $command = new DeleteOneToOneConversation();
        $command->authorizerId = $sender->id();
        $command->conversationId = $conversationStarted->aggregateId()->id();
        $command->metadata = $this->mockMetadata();

        $handler = new DeleteOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue()
        );

        $handler->handle($command);

        /** @var OneToOneConversationDeletedBySender $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[1];
        /** @var OneToOneConversationDeletedBySender $queuedEvent */
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
