<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Aggregate\Folder;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class FolderRenamed implements EventTransformedFolder
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var string
     */
    private $name;

    public function name(): string
    {
        return $this->name;
    }

    public function transformFolder(Folder $folder): Folder
    {
        return Folder::fromProperties(
            $folder->id(),
            $this->name,
            $folder->parent(),
            $folder->ownerId(),
            $folder->deleted()
        );
    }

    public static function fromProperties(
        Id $authorizerId,
        array $metadata,
        Id $folderId,
        string $name
    ): FolderRenamed {
        $event = new self(
            $folderId,
            $authorizerId,
            $metadata
        );

        $event->name = $name;

        return $event;
    }
}
