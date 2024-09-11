<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\LoggedFolderOpened\Event;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class LoggedFolderOpened implements Event
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $folderId;

    public function folderId(): Id
    {
        return $this->folderId;
    }

    public static function fromProperties(
        Id $authorizerId,
        array $metadata,
        Id $folderId
    ): LoggedFolderOpened {
        $aggregateId = Id::createNew();

        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->folderId = $folderId;

        return $event;
    }
}
