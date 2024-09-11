<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Command;

class SignIn
{
    /**
     * @var string
     */
    public $withUsernameOrEmail;

    /**
     * @var string
     */
    public $withPassword;

    /**
     * @var string
     */
    public $byDeviceLabel;

    /**
     * @var string
     */
    public $withIp;

    /**
     * @var array
     */
    public $metadata;
}
