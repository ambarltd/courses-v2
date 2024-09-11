<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\LoggedRootFolderOpened\Event;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class LoggedRootFolderOpened implements Event
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $ownerId;

    public function ownerId(): Id
    {
        return $this->ownerId;
    }

    public static function fromProperties(
        Id $authorizerId,
        array $metadata
    ): self {
        $aggregateId = Id::createNew();

        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->ownerId = $authorizerId;

        return $event;
    }
}
