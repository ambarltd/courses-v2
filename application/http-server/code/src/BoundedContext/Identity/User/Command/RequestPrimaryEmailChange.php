<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Command;

class RequestPrimaryEmailChange
{
    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $newEmailRequested;

    /**
     * @var array
     */
    public $metadata;
}
