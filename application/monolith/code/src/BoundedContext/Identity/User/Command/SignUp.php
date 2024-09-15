<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Command;

class SignUp
{
    public string $primaryEmail;

    public string $password;

    public string $username;

    public string $termsOfUseAccepted;

    public array $metadata;
}
