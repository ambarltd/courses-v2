<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Aggregate\Folder;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class FolderDeleted implements EventTransformedFolder
{
    use EventWithAuthorizerAndNoSourceTrait;

    public function transformFolder(Folder $folder): Folder
    {
        return Folder::fromProperties(
            $folder->id(),
            $folder->name(),
            $folder->parent(),
            $folder->ownerId(),
            true
        );
    }

    public static function fromProperties(
        Id $authorizerId,
        array $metadata,
        Id $folderId
    ): FolderDeleted {
        $event = new self(
            $folderId,
            $authorizerId,
            $metadata
        );

        return $event;
    }
}
