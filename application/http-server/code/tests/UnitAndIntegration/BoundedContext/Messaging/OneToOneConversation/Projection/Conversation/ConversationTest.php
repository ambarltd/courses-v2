<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation\Conversation;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\UnitTestBase;

class ConversationTest extends UnitTestBase
{
    /**
     * @test
     */
    public function testNotExpired(): void
    {
        $conversationId = Id::createNew();
        $senderId = Id::createNew();
        $recipientId = Id::createNew();
        $expirationDate = new \DateTimeImmutable('tomorrow');
        $maxNumberOfViews = 32;
        $latestActivity = new \DateTimeImmutable('-5 minutes');

        $conversation = Conversation::fromProperties(
            $conversationId->id(),
            $senderId->id(),
            $recipientId->id(),
            $expirationDate,
            $maxNumberOfViews,
            $latestActivity
        );

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('pushed', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'pushed',
            ],
            $conversation->serialize()
        );

        $conversation->pulledBySender();

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('pulled_by_sender', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'pulled_by_sender',
            ],
            $conversation->serialize()
        );

        $conversation->rejectedByRecipient();

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('rejected_by_recipient', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'rejected_by_recipient',
            ],
            $conversation->serialize()
        );

        $conversation->deletedBySender();

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('deleted_by_sender', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'deleted_by_sender',
            ],
            $conversation->serialize()
        );
    }

    /**
     * @test
     */
    public function testExpired(): void
    {
        $conversationId = Id::createNew();
        $senderId = Id::createNew();
        $recipientId = Id::createNew();
        $expirationDate = new \DateTimeImmutable('yesterday');
        $maxNumberOfViews = 32;
        $latestActivity = new \DateTimeImmutable('-5 minutes');

        $conversation = Conversation::fromProperties(
            $conversationId->id(),
            $senderId->id(),
            $recipientId->id(),
            $expirationDate,
            $maxNumberOfViews,
            $latestActivity
        );

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('pushed', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'pushed_expired',
            ],
            $conversation->serialize()
        );

        $conversation->pulledBySender();

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('pulled_by_sender', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'pulled_by_sender_expired',
            ],
            $conversation->serialize()
        );

        $conversation->rejectedByRecipient();

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('rejected_by_recipient', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'rejected_by_recipient_expired',
            ],
            $conversation->serialize()
        );

        $conversation->deletedBySender();

        Assert::assertInstanceOf(Conversation::class, $conversation);
        Assert::assertEquals($conversationId->id(), $conversation->getConversationId());
        Assert::assertEquals($senderId->id(), $conversation->getSenderId());
        Assert::assertEquals($recipientId->id(), $conversation->getRecipientId());
        Assert::assertEquals($expirationDate, $conversation->getExpirationDate());
        Assert::assertEquals($maxNumberOfViews, $conversation->getMaxNumberOfViews());
        Assert::assertEquals('deleted_by_sender', $conversation->getPushStatus());
        Assert::assertEquals($latestActivity, $conversation->getLatestActivity());
        Assert::assertEquals(
            [
                'conversationId' => $conversationId->id(),
                'senderId' => $senderId->id(),
                'recipientId' => $recipientId->id(),
                'expirationDate' => $expirationDate->format(\DATE_RFC3339),
                'maxNumberOfViews' => $maxNumberOfViews,
                'latestActivity' => $latestActivity->format(\DATE_RFC3339),
                'status' => 'deleted_by_sender_expired',
            ],
            $conversation->serialize()
        );
    }
}
