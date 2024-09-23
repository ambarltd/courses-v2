<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Command;

class RequestPrimaryEmailChange
{
    public string $authenticatedUserId;

    public string $password;

    public string $newEmailRequested;

    /** @var array<string, mixed> */
    public array $metadata;
}
