<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Event;

use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\Aggregate\OneToOneConversation;
use Galeas\Api\BoundedContext\Messaging\OneToOneConversation\ValueObject\PushStatus;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class OneToOneConversationDeletedBySender implements EventTransformedOneToOneConversation
{
    use EventWithAuthorizerAndNoSourceTrait;

    public static function fromProperties(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata
    ): OneToOneConversationDeletedBySender {
        $event = new self($aggregateId, $authorizerId, $metadata);

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function transformOneToOneConversation(OneToOneConversation $oneToOneConversation): OneToOneConversation
    {
        return OneToOneConversation::fromProperties(
            $oneToOneConversation->id(),
            $oneToOneConversation->sender(),
            $oneToOneConversation->recipient(),
            $oneToOneConversation->maxNumberOfViews(),
            $oneToOneConversation->expirationDate(),
            PushStatus::deletedBySender()
        );
    }
}
