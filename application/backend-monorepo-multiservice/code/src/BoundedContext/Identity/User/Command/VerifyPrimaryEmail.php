<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Command;

class VerifyPrimaryEmail
{
    /** @var array<string, mixed> */
    public array $metadata;

    public string $verificationCode;
}
