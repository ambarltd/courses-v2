<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationDeletedBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationPulledBySender;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationRejectedByRecipient;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event\OneToOneConversationStarted;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation\Conversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation\ConversationProcessor;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ConversationProcessorTest extends KernelTestBase
{
    private function conversationProcessor(): ConversationProcessor
    {
        return $conversationProcessor = $this->getContainer()
                ->get(ConversationProcessor::class);
    }

    private function sampleConversationStartedEvent(): OneToOneConversationStarted
    {
        $authorizerId = Id::createNew();
        $metadata = [];
        $sender = Id::createNew();
        $recipient = Id::createNew();
        $expirationDate = new \DateTimeImmutable('tomorrow');
        $maxNumberOfViews = 32;

        return OneToOneConversationStarted::fromProperties(
            $authorizerId,
            $metadata,
            $sender,
            $recipient,
            $expirationDate,
            $maxNumberOfViews
        );
    }

    /**
     * @test
     */
    public function testOneToOneConversationStarted(): void
    {
        $conversationStarted = $this->sampleConversationStartedEvent();
        $this->conversationProcessor()->process($conversationStarted);

        $conversations = $this->findAllConversations();

        Assert::assertCount(
            1,
            $conversations
        );

        $conversationProjection = $conversations[0];

        Assert::assertEquals(
            $conversationStarted->aggregateId()->id(),
            $conversationProjection->getConversationId()
        );
        Assert::assertEquals(
            $conversationStarted->sender()->id(),
            $conversationProjection->getSenderId()
        );
        Assert::assertEquals(
            $conversationStarted->recipient()->id(),
            $conversationProjection->getRecipientId()
        );
        Assert::assertEquals(
            $conversationStarted->expirationDate(),
            $conversationProjection->getExpirationDate()
        );
        Assert::assertEquals(
            $conversationStarted->maxNumberOfViews(),
            $conversationProjection->getMaxNumberOfViews()
        );
        Assert::assertEquals(
            'pushed',
            $conversationProjection->getPushStatus()
        );
        Assert::assertEquals(
            $conversationStarted->eventOccurredOn(),
            $conversationProjection->getLatestActivity()
        );
    }

    /**
     * @test
     */
    public function testOneToOneConversationPulledBySender(): void
    {
        $conversationStarted = $this->sampleConversationStartedEvent();
        $this->conversationProcessor()->process($conversationStarted);

        $conversationPulledBySender = OneToOneConversationPulledBySender::fromProperties(
            $conversationStarted->aggregateId(),
            $conversationStarted->sender(),
            []
        );
        $this->conversationProcessor()->process($conversationPulledBySender);

        $conversations = $this->findAllConversations();

        Assert::assertCount(
            1,
            $conversations
        );

        $conversationProjection = $conversations[0];

        Assert::assertEquals(
            $conversationStarted->aggregateId()->id(),
            $conversationProjection->getConversationId()
        );
        Assert::assertEquals(
            $conversationStarted->sender()->id(),
            $conversationProjection->getSenderId()
        );
        Assert::assertEquals(
            $conversationStarted->recipient()->id(),
            $conversationProjection->getRecipientId()
        );
        Assert::assertEquals(
            $conversationStarted->expirationDate(),
            $conversationProjection->getExpirationDate()
        );
        Assert::assertEquals(
            $conversationStarted->maxNumberOfViews(),
            $conversationProjection->getMaxNumberOfViews()
        );
        Assert::assertEquals(
            'pulled_by_sender',
            $conversationProjection->getPushStatus()
        );
        Assert::assertEquals(
            $conversationStarted->eventOccurredOn(),
            $conversationProjection->getLatestActivity()
        );
    }

    /**
     * @test
     */
    public function testOneToOneConversationRejectedByRecipient(): void
    {
        $conversationStarted = $this->sampleConversationStartedEvent();
        $this->conversationProcessor()->process($conversationStarted);

        $conversationPulledBySender = OneToOneConversationRejectedByRecipient::fromProperties(
            $conversationStarted->aggregateId(),
            $conversationStarted->recipient(),
            []
        );
        $this->conversationProcessor()->process($conversationPulledBySender);

        $conversations = $this->findAllConversations();

        Assert::assertCount(
            1,
            $conversations
        );

        $conversationProjection = $conversations[0];

        Assert::assertEquals(
            $conversationStarted->aggregateId()->id(),
            $conversationProjection->getConversationId()
        );
        Assert::assertEquals(
            $conversationStarted->sender()->id(),
            $conversationProjection->getSenderId()
        );
        Assert::assertEquals(
            $conversationStarted->recipient()->id(),
            $conversationProjection->getRecipientId()
        );
        Assert::assertEquals(
            $conversationStarted->expirationDate(),
            $conversationProjection->getExpirationDate()
        );
        Assert::assertEquals(
            $conversationStarted->maxNumberOfViews(),
            $conversationProjection->getMaxNumberOfViews()
        );
        Assert::assertEquals(
            'rejected_by_recipient',
            $conversationProjection->getPushStatus()
        );
        Assert::assertEquals(
            $conversationStarted->eventOccurredOn(),
            $conversationProjection->getLatestActivity()
        );
    }

    /**
     * @test
     */
    public function testOneToOneConversationDeletedBySender(): void
    {
        $conversationStarted = $this->sampleConversationStartedEvent();
        $this->conversationProcessor()->process($conversationStarted);

        $conversationPulledBySender = OneToOneConversationDeletedBySender::fromProperties(
            $conversationStarted->aggregateId(),
            $conversationStarted->sender(),
            []
        );
        $this->conversationProcessor()->process($conversationPulledBySender);

        $conversations = $this->findAllConversations();

        Assert::assertCount(
            1,
            $conversations
        );

        $conversationProjection = $conversations[0];

        Assert::assertEquals(
            $conversationStarted->aggregateId()->id(),
            $conversationProjection->getConversationId()
        );
        Assert::assertEquals(
            $conversationStarted->sender()->id(),
            $conversationProjection->getSenderId()
        );
        Assert::assertEquals(
            $conversationStarted->recipient()->id(),
            $conversationProjection->getRecipientId()
        );
        Assert::assertEquals(
            $conversationStarted->expirationDate(),
            $conversationProjection->getExpirationDate()
        );
        Assert::assertEquals(
            $conversationStarted->maxNumberOfViews(),
            $conversationProjection->getMaxNumberOfViews()
        );
        Assert::assertEquals(
            'deleted_by_sender',
            $conversationProjection->getPushStatus()
        );
        Assert::assertEquals(
            $conversationStarted->eventOccurredOn(),
            $conversationProjection->getLatestActivity()
        );
    }

    /**
     * @return Conversation[]
     */
    private function findAllConversations()
    {
        return array_values(
            $this->getProjectionDocumentManager()
                ->createQueryBuilder(Conversation::class)
                ->getQuery()
                ->execute()
                ->toArray()
        );
    }
}
