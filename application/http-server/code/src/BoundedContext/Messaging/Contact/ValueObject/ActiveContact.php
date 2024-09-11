<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\ValueObject;

use Galeas\Api\Common\Id\Id;

/**
 * Data about who requested who (or if the contact was created from a request) is not available here. It would
 * require a different aggregate root model. If you need to use this data, handle it from the read model, because
 * there are no business invariants that would warrant a change in the aggregate model.
 */
class ActiveContact
{
    /**
     * Id of an @see \Galeas\Api\BoundedContext\Identity\User\Aggregate\User.
     *
     * @var Id
     */
    private $firstContact;

    /**
     * Id of an @see \Galeas\Api\BoundedContext\Identity\User\Aggregate\User.
     *
     * @var Id
     */
    private $secondContact;

    private function __construct()
    {
    }

    public function firstContact(): Id
    {
        return $this->firstContact;
    }

    public function secondContact(): Id
    {
        return $this->secondContact;
    }

    public static function fromContacts(
        Id $firstContact,
        Id $secondContact
    ): ActiveContact {
        $activeContact = new self();
        $activeContact->firstContact = $firstContact;
        $activeContact->secondContact = $secondContact;

        return $activeContact;
    }
}
