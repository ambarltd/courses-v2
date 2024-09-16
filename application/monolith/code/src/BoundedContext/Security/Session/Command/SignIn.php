<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Command;

class SignIn
{
    public string $withUsernameOrEmail;

    public string $withPassword;

    public string $byDeviceLabel;

    public string $withIp;

    public array $metadata;
}
