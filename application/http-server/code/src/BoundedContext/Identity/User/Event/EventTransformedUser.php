<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\Common\Event\Event;

interface EventTransformedUser extends Event
{
    public function transformUser(User $user): User;
}
