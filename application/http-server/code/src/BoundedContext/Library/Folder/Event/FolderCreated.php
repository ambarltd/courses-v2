<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Aggregate\Folder;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class FolderCreated implements EventCreatedFolder
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var Id | null
     */
    private $parent;

    /**
     * @var Id
     */
    private $ownerId;

    public function name(): string
    {
        return $this->name;
    }

    public function parent(): ?Id
    {
        return $this->parent;
    }

    public function ownerId(): Id
    {
        return $this->ownerId;
    }

    public function createFolder(): Folder
    {
        return Folder::fromProperties(
            $this->aggregateId(),
            $this->name,
            $this->parent,
            $this->ownerId,
            false
        );
    }

    /**
     * @param Id | null $parent
     */
    public static function fromProperties(
        Id $authorizerId,
        array $metadata,
        string $name,
        ?Id $parent
    ): FolderCreated {
        $aggregateId = Id::createNew();
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->name = $name;
        $event->parent = $parent;
        $event->ownerId = $authorizerId;

        return $event;
    }
}
