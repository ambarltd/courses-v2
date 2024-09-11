<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Library\Folder\Event;

use Galeas\Api\BoundedContext\Library\Folder\Aggregate\Folder;
use Galeas\Api\Common\Event\Event;

interface EventCreatedFolder extends Event
{
    public function createFolder(): Folder;
}
