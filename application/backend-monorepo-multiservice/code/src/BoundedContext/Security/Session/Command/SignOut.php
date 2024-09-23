<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Command;

class SignOut
{
    public string $authenticatedUserId;

    public string $withIp;

    public string $withSessionToken;

    /** @var array<string, mixed> */
    public array $metadata;
}
