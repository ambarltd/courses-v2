<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Command;

class RejectContactRequest
{
    /**
     * @var string
     */
    public $rejectedContact;

    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var array
     */
    public $metadata;
}
