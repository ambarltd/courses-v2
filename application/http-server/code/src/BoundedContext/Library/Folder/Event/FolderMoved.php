<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Aggregate\Folder;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class FolderMoved implements EventTransformedFolder
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id|null
     */
    private $destinationId;

    public function destinationId(): ?Id
    {
        return $this->destinationId;
    }

    public function transformFolder(Folder $folder): Folder
    {
        return Folder::fromProperties(
            $folder->id(),
            $folder->name(),
            $this->destinationId,
            $folder->ownerId(),
            $folder->deleted()
        );
    }

    public static function fromProperties(
        Id $authorizerId,
        array $metadata,
        Id $folderId,
        ?Id $destinationId
    ): FolderMoved {
        $event = new self(
            $folderId,
            $authorizerId,
            $metadata
        );

        $event->destinationId = $destinationId;

        return $event;
    }
}
