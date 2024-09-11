<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\ValueObject;

use Galeas\Api\Common\Id\Id;

/**
 * The contact could have been deleted for several reasons (e.g. deletion after being active, request cancelled, request rejected).
 * Data about whether the deleted contact was rejected/deleted/cancelled is not available here. It would require a different aggregate root model.
 * Data about who rejected/deleted/cancelled is not available here. It would require a different aggregate root model.
 * If you need to use this data, handle it from the read model, because there are no business invariants that would warrant a change in the
 * aggregate root model.
 */
class InactiveContact
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
    ): InactiveContact {
        $inactiveContact = new self();
        $inactiveContact->firstContact = $firstContact;
        $inactiveContact->secondContact = $secondContact;

        return $inactiveContact;
    }
}
