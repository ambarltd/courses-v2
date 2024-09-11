<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Projection\Conversation;

class Conversation
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $senderId;

    /**
     * @var string
     */
    private $recipientId;

    /**
     * @var \DateTimeImmutable|null
     */
    private $expirationDate;

    /**
     * @var int|null
     */
    private $maxNumberOfViews;

    /**
     * @var string
     */
    private $pushStatus;

    /**
     * Changed only by new comments. Without comments, it defaults to the creation date of the conversation.
     *
     * @var \DateTimeImmutable
     */
    private $latestActivity;

    public function getConversationId(): string
    {
        return $this->id;
    }

    public function getSenderId(): string
    {
        return $this->senderId;
    }

    public function getRecipientId(): string
    {
        return $this->recipientId;
    }

    public function getExpirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function getMaxNumberOfViews(): ?int
    {
        return $this->maxNumberOfViews;
    }

    public function getPushStatus(): string
    {
        return $this->pushStatus;
    }

    public function getLatestActivity(): \DateTimeImmutable
    {
        return $this->latestActivity;
    }

    /**
     * @return Conversation
     */
    public static function fromProperties(
        string $conversationId,
        string $senderId,
        string $recipientId,
        ?\DateTimeImmutable $expirationDate,
        ?int $maxNumberOfViews,
        \DateTimeImmutable $latestActivity
    ): self {
        $conversation = new self();
        $conversation->id = $conversationId;
        $conversation->senderId = $senderId;
        $conversation->recipientId = $recipientId;
        $conversation->expirationDate = $expirationDate;
        $conversation->maxNumberOfViews = $maxNumberOfViews;
        $conversation->pushStatus = 'pushed';
        $conversation->latestActivity = $latestActivity;

        return $conversation;
    }

    public function pulledBySender(): self
    {
        $this->pushStatus = 'pulled_by_sender';

        return $this;
    }

    public function rejectedByRecipient(): self
    {
        $this->pushStatus = 'rejected_by_recipient';

        return $this;
    }

    public function deletedBySender(): self
    {
        $this->pushStatus = 'deleted_by_sender';

        return $this;
    }

    public function serialize(): array
    {
        $status = $this->pushStatus;

        if (
            $this->expirationDate &&
            new \DateTimeImmutable() > $this->expirationDate
        ) {
            $status = $status.'_expired';
        }

        return [
            'conversationId' => $this->id,
            'senderId' => $this->senderId,
            'recipientId' => $this->recipientId,
            'expirationDate' => $this->expirationDate ? $this->expirationDate->format(\DATE_RFC3339) : null,
            'maxNumberOfViews' => $this->maxNumberOfViews,
            'latestActivity' => $this->latestActivity->format(\DATE_RFC3339),
            'status' => $status,
        ];
    }
}
