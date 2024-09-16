<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail;

class RequestedChange
{
    private function __construct()
    {
    }

    public static function setStatus(): self
    {
        return new self();
    }
}