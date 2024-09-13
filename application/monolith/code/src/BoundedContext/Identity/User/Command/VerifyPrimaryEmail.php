<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Command;

class VerifyPrimaryEmail
{
    /**
     * @var array
     */
    public $metadata;

    /**
     * @var string
     */
    public $verificationCode;
}
