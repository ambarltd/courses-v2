<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Command;

class DeleteFolder
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
    public $folderId;
}
