<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Command;

class AcceptContactRequest
{
    /**
     * @var string
     */
    public $acceptedContact;

    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var array
     */
    public $metadata;
}
