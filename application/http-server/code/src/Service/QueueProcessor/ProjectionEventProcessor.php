<?php

declare(strict_types=1);

namespace Galeas\Api\Service\QueueProcessor;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\ProjectionCannotProcess;

interface ProjectionEventProcessor
{
    /**
     * @throws ProjectionCannotProcess
     */
    public function process(Event $event): void;
}
