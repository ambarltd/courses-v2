<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\TakenEmail\Event;

use Galeas\Api\BoundedContext\Identity\TakenEmail\Aggregate\TakenEmail;
use Galeas\Api\Common\Event\Event;

interface EventCreatedTakenEmail extends Event
{
    public function createTakenEmail(): TakenEmail;
}
