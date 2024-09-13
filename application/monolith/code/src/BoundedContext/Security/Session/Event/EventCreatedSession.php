<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\Common\Event\Event;

interface EventCreatedSession extends Event
{
    public function createSession(): Session;
}
