<?php

declare(strict_types=1);

namespace Galeas\Api\Service\Queue;

use Galeas\Api\Common\Event\Event;
use Galeas\Api\Common\ExceptionBase\QueuingFailure;

interface Queue
{
    /**
     * @throws QueuingFailure
     */
    public function enqueue(Event $event): void;
}
