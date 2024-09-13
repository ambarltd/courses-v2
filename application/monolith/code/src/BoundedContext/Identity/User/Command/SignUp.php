<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Command;

class SignUp
{
    /**
     * @var string
     */
    public $primaryEmail;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $username;

    /**
     * @var bool
     */
    public $termsOfUseAccepted;

    /**
     * @var array
     */
    public $metadata;
}
