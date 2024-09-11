<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class OneToOneConversationStarted implements EventCreatedOneToOneConversation
{
    use EventWithAuthorizerAndNoSourceTrait;

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

    public static function fromProperties(
        Id $authorizerId,
        array $metadata,
        Id $sender,
        Id $recipient,
        ?\DateTimeImmutable $expirationDate,
        ?int $maxNumberOfViews
    ): OneToOneConversationStarted {
        $aggregateId = Id::createNew();
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->sender = $sender;
        $event->recipient = $recipient;
        $event->expirationDate = $expirationDate;
        $event->maxNumberOfViews = $maxNumberOfViews;

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function createOneToOneConversation(): OneToOneConversation
    {
        return OneToOneConversation::fromProperties(
            $this->aggregateId(),
            $this->sender(),
            $this->recipient(),
            $this->maxNumberOfViews(),
            $this->expirationDate(),
            PushStatus::pushed()
        );
    }
}
