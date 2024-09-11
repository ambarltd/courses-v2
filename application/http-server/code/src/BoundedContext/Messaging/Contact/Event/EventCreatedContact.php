<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\Common\Event\Event;

interface EventCreatedContact extends Event
{
    public function createContact(): Contact;
}
