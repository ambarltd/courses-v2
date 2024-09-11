<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Command;

class RenameFolder
{
    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var array
     */
    public $metadata;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $folderId;
}
