<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

/**
 * Describes a start of one to one conversation between two @see User.
 */
class OneToOneConversation implements Aggregate
{
    use AggregateTrait;

    /**
     * @var Id
     */
    private $sender;

    /**
     * @var Id
     */
    private $recipient;

    /**
     * @var int|null
     */
    private $maxNumberOfViews;

    /**
     * @var \DateTimeImmutable|null
     */
    private $expirationDate;

    /**
     * @var PushStatus
     */
    private $pushStatus;

    public function sender(): Id
    {
        return $this->sender;
    }

    public function recipient(): Id
    {
        return $this->recipient;
    }

    public function maxNumberOfViews(): ?int
    {
        return $this->maxNumberOfViews;
    }

    public function expirationDate(): ?\DateTimeImmutable
    {
        return $this->expirationDate;
    }

    public function pushStatus(): PushStatus
    {
        return $this->pushStatus;
    }

    public static function fromProperties(
        Id $id,
        Id $sender,
        Id $recipient,
        ?int $maxNumberOfViews,
        ?\DateTimeImmutable $expirationDate,
        PushStatus $pushStatus
    ): OneToOneConversation {
        $conversation = new self($id);
        $conversation->sender = $sender;
        $conversation->recipient = $recipient;
        $conversation->maxNumberOfViews = $maxNumberOfViews;
        $conversation->expirationDate = $expirationDate;
        $conversation->pushStatus = $pushStatus;

        return $conversation;
    }
}
