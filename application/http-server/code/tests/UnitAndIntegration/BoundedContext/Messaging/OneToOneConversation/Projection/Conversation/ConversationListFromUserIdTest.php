<?php

declare(strict_types=1);

namespace Tests\Galeas\Api\UnitAndIntegration\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation\Conversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation\ConversationListFromUserId;
use Galeas\Api\Common\Id\Id;
use PHPUnit\Framework\Assert;
use Tests\Galeas\Api\UnitAndIntegration\KernelTestBase;

class ConversationListFromUserIdTest extends KernelTestBase
{
    private function conversationListFromUserId(): ConversationListFromUserId
    {
        return $this->getContainer()
            ->get(ConversationListFromUserId::class);
    }

    /**
     * @test
     */
    public function testConversationListFromUserId(): void
    {
        $sean = (Id::createNew())->id();
        $marina = (Id::createNew())->id();
        $lucian = (Id::createNew())->id();
        $luis = (Id::createNew())->id();

        $seanToMarina = (Id::createNew())->id();
        $seanToMarinaExpiresTomorrow = (Id::createNew())->id();
        $marinaToSean = (Id::createNew())->id();
        $seanToLucian = (Id::createNew())->id();
        $seanToLucianExpiredYesterday = (Id::createNew())->id();
        $seanToLucianPulled = (Id::createNew())->id();
        $seanToLucianRejected = (Id::createNew())->id();
        $seanToLucianDeleted = (Id::createNew())->id();
        $seanToLucianPulledAndExpiredYesterday = (Id::createNew())->id();
        $seanToLucianRejectedAndExpiredYesterday = (Id::createNew())->id();

        $tomorrow = new \DateTimeImmutable('tomorrow');
        $yesterday = new \DateTimeImmutable('yesterday');

        /** @var Conversation[] $conversations */
        $conversations = [
            Conversation::fromProperties(
                $seanToMarina,
                $sean,
                $marina,
                null,
                null,
                new \DateTimeImmutable()
            ),
            Conversation::fromProperties(
                $seanToMarinaExpiresTomorrow,
                $sean,
                $marina,
                $tomorrow,
                null,
                new \DateTimeImmutable()
            ),
            Conversation::fromProperties(
                $marinaToSean,
                $marina,
                $sean,
                null,
                null,
                new \DateTimeImmutable()
            ),
            Conversation::fromProperties(
                $seanToLucian,
                $sean,
                $lucian,
                null,
                null,
                new \DateTimeImmutable()
            ),
            Conversation::fromProperties(
                $seanToLucianExpiredYesterday,
                $sean,
                $lucian,
                $yesterday,
                null,
                new \DateTimeImmutable()
            ),
            Conversation::fromProperties(
                $seanToLucianPulled,
                $sean,
                $lucian,
                null,
                null,
                new \DateTimeImmutable()
            )->pulledBySender(),
            Conversation::fromProperties(
                $seanToLucianRejected,
                $sean,
                $lucian,
                null,
                null,
                new \DateTimeImmutable()
            )->rejectedByRecipient(),
            Conversation::fromProperties(
                $seanToLucianDeleted,
                $sean,
                $lucian,
                null,
                null,
                new \DateTimeImmutable()
            )->deletedBySender(),
            Conversation::fromProperties(
                $seanToLucianPulledAndExpiredYesterday,
                $sean,
                $lucian,
                $yesterday,
                null,
                new \DateTimeImmutable()
            )->pulledBySender(),
            Conversation::fromProperties(
                $seanToLucianRejectedAndExpiredYesterday,
                $sean,
                $lucian,
                $yesterday,
                null,
                new \DateTimeImmutable()
            )->rejectedByRecipient(),
        ];

        foreach ($conversations as $conversation) {
            $this->getProjectionDocumentManager()->persist($conversation);
        }
        $this->getProjectionDocumentManager()->flush();

        Assert::assertEquals(
            [
                [
                    'conversationId' => $seanToLucianRejectedAndExpiredYesterday,
                    'senderId' => $sean,
                    'recipientId' => $lucian,
                    'expirationDate' => $yesterday->format(\DATE_RFC3339),
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[9]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'rejected_by_recipient_expired',
                ],
                [
                    'conversationId' => $seanToLucianPulledAndExpiredYesterday,
                    'senderId' => $sean,
                    'recipientId' => $lucian,
                    'expirationDate' => $yesterday->format(\DATE_RFC3339),
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[8]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pulled_by_sender_expired',
                ],
                [
                    'conversationId' => $seanToLucianRejected,
                    'senderId' => $sean,
                    'recipientId' => $lucian,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[6]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'rejected_by_recipient',
                ],
                [
                    'conversationId' => $seanToLucianPulled,
                    'senderId' => $sean,
                    'recipientId' => $lucian,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[5]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pulled_by_sender',
                ],
                [
                    'conversationId' => $seanToLucianExpiredYesterday,
                    'senderId' => $sean,
                    'recipientId' => $lucian,
                    'expirationDate' => $yesterday->format(\DATE_RFC3339),
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[4]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed_expired',
                ],
                [
                    'conversationId' => $seanToLucian,
                    'senderId' => $sean,
                    'recipientId' => $lucian,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[3]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
                [
                    'conversationId' => $marinaToSean,
                    'senderId' => $marina,
                    'recipientId' => $sean,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[2]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
                [
                    'conversationId' => $seanToMarinaExpiresTomorrow,
                    'senderId' => $sean,
                    'recipientId' => $marina,
                    'expirationDate' => $tomorrow->format(\DATE_RFC3339),
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[1]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
                [
                    'conversationId' => $seanToMarina,
                    'senderId' => $sean,
                    'recipientId' => $marina,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[0]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
            ],
            $this->conversationListFromUserId()->conversationListFromUserId($sean)
        );
        Assert::assertEquals(
            [
                [
                    'conversationId' => $marinaToSean,
                    'senderId' => $marina,
                    'recipientId' => $sean,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[2]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
                [
                    'conversationId' => $seanToMarinaExpiresTomorrow,
                    'senderId' => $sean,
                    'recipientId' => $marina,
                    'expirationDate' => $tomorrow->format(\DATE_RFC3339),
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[1]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
                [
                    'conversationId' => $seanToMarina,
                    'senderId' => $sean,
                    'recipientId' => $marina,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[0]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
            ],
            $this->conversationListFromUserId()->conversationListFromUserId($marina)
        );
        Assert::assertEquals(
            [
                [
                    'conversationId' => $seanToLucian,
                    'senderId' => $sean,
                    'recipientId' => $lucian,
                    'expirationDate' => null,
                    'maxNumberOfViews' => null,
                    'latestActivity' => $conversations[3]->getLatestActivity()->format(\DATE_RFC3339),
                    'status' => 'pushed',
                ],
            ],
            $this->conversationListFromUserId()->conversationListFromUserId($lucian)
        );
        Assert::assertEquals(
            [],
            $this->conversationListFromUserId()->conversationListFromUserId($luis)
        );
    }
}
