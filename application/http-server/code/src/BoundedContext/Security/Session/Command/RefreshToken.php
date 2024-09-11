<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Command;

class RefreshToken
{
    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var string
     */
    public $withIp;

    /**
     * @var string
     */
    public $withSessionToken;

    /**
     * @var array
     */
    public $metadata;
}
