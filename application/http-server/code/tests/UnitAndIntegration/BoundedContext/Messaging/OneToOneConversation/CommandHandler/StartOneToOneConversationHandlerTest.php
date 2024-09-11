<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\CommandHandler;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Command\StartOneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\AreParticipantsContacts;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\StartOneToOneConversationHandler;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\HandlerTestBase;
use Tests\Galeas\Api\UnitAndIntegration\Primitive\PrimitiveValidation\Id\ValidIds;

class StartOneToOneConversationHandlerTest extends HandlerTestBase
{
    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\CannotHaveConversationWithSelf
     */
    public function testCannotHaveConversationWithSelf(): void
    {
        $command = new StartOneToOneConversation();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->recipient = ValidIds::listValidIds()[0];
        $command->expirationDate = (new \DateTimeImmutable('+1 minute'))->format(\DATE_RFC3339);
        $command->maxNumberOfViews = 54;
        $command->metadata = $this->mockMetadata();

        $handler = new StartOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                AreParticipantsContacts::class,
                'areParticipantsContacts',
                true
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\ParticipantsAreNotContacts
     */
    public function testParticipantsAreNotContacts(): void
    {
        $command = new StartOneToOneConversation();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->recipient = ValidIds::listValidIds()[1];
        $command->expirationDate = (new \DateTimeImmutable('+1 minute'))->format(\DATE_RFC3339);
        $command->maxNumberOfViews = 54;
        $command->metadata = $this->mockMetadata();

        $handler = new StartOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                AreParticipantsContacts::class,
                'areParticipantsContacts',
                function (string $participant1, string $participant2) use ($command): bool {
                    if (
                        $participant1 === $command->authorizerId &&
                        $participant2 === $command->recipient
                    ) {
                        return false;
                    }

                    return true;
                }
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\ExpirationDateIsInThePast
     */
    public function testExpirationDateIsInThePast(): void
    {
        $command = new StartOneToOneConversation();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->recipient = ValidIds::listValidIds()[1];
        $command->expirationDate = (new \DateTimeImmutable('-1 second'))->format(\DATE_RFC3339);
        $command->maxNumberOfViews = 54;
        $command->metadata = $this->mockMetadata();

        $handler = new StartOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                AreParticipantsContacts::class,
                'areParticipantsContacts',
                true
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\MaxNumberOfViewsIsTooLarge
     */
    public function testMaxNumberOfViewsIsTooLarge(): void
    {
        $command = new StartOneToOneConversation();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->recipient = ValidIds::listValidIds()[1];
        $command->expirationDate = (new \DateTimeImmutable('+1 minute'))->format(\DATE_RFC3339);
        $command->maxNumberOfViews = 1000001;
        $command->metadata = $this->mockMetadata();

        $handler = new StartOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                AreParticipantsContacts::class,
                'areParticipantsContacts',
                true
            )
        );

        $handler->handle($command);
    }

    /**
     * @expectedException \Galeas\Api\BoundedContext\Messaging\OneToOneConversation\CommandHandler\StartOneToOneConversation\MaxNumberOfViewsIsTooSmall
     */
    public function testMaxNumberOfViewsIsTooSmall(): void
    {
        $command = new StartOneToOneConversation();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->recipient = ValidIds::listValidIds()[1];
        $command->expirationDate = (new \DateTimeImmutable('+1 minute'))->format(\DATE_RFC3339);
        $command->maxNumberOfViews = 0;
        $command->metadata = $this->mockMetadata();

        $handler = new StartOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithReturnValue(
                AreParticipantsContacts::class,
                'areParticipantsContacts',
                true
            )
        );

        $handler->handle($command);
    }

    /**
     * @test
     */
    public function testHandle(): void
    {
        $command = new StartOneToOneConversation();
        $command->authorizerId = ValidIds::listValidIds()[0];
        $command->recipient = ValidIds::listValidIds()[1];
        $command->expirationDate = (new \DateTimeImmutable('+1 minute'))->format(\DATE_RFC3339);
        $command->maxNumberOfViews = 20;
        $command->metadata = $this->mockMetadata();

        $handler = new StartOneToOneConversationHandler(
            $this->getInMemoryEventStore(),
            $this->getInMemoryQueue(),
            $this->mockForCommandHandlerWithCallback(
                AreParticipantsContacts::class,
                'areParticipantsContacts',
                function (string $participant1, string $participant2) use ($command): bool {
                    if (
                        $participant1 === $command->authorizerId &&
                        $participant2 === $command->recipient
                    ) {
                        return true;
                    }

                    return false;
                }
            )
        );

        $handler->handle($command);

        /** @var OneToOneConversationStarted $storedEvent */
        $storedEvent = $this->getInMemoryEventStore()->storedEvents()[0];
        /** @var OneToOneConversationStarted $queuedEvent */
        $queuedEvent = $this->getInMemoryQueue()->queuedEvents()[0];

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
            $command->authorizerId,
            $storedEvent->sender()->id()
        );

        Assert::assertEquals(
            $command->recipient,
            $storedEvent->recipient()->id()
        );

        Assert::assertEquals(
            null,
            $storedEvent->sourceEventId()
        );

        Assert::assertInstanceOf(
            \DateTimeImmutable::class,
            $storedEvent->eventOccurredOn()
        );

        Assert::assertInstanceOf(
            Id::class,
            $storedEvent->aggregateId()
        );
    }
}
