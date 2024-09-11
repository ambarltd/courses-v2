<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Command;

class CancelContactRequest
{
    /**
     * @var string
     */
    public $cancelledContact;

    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var array
     */
    public $metadata;
}
