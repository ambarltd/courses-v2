<?php

declare(strict_types=1);

namespace Galeas\Api\Service\QueueProcessor;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\CommonException\ProjectionCannotProcess;

interface EventProjector
{
    /**
     * @throws ProjectionCannotProcess
     */
    public function project(Event $event): void;
}
