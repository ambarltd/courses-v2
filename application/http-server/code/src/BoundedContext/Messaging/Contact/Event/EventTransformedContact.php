<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\Common\Event\Event;

interface EventTransformedContact extends Event
{
    public function transformContact(Contact $contact): Contact;
}
