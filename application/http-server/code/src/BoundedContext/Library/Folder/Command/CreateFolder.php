<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Command;

class CreateFolder
{
    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string | null
     */
    public $parentId;

    /**
     * @var array
     */
    public $metadata;
}
