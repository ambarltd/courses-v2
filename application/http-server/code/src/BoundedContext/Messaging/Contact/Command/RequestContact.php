<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Command;

class RequestContact
{
    /**
     * @var string
     */
    public $requestedContact;

    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var array
     */
    public $metadata;
}
