<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Command;

class DeleteContact
{
    /**
     * @var string
     */
    public $deletedContact;

    /**
     * @var string
     */
    public $authorizerId;

    /**
     * @var array
     */
    public $metadata;
}
