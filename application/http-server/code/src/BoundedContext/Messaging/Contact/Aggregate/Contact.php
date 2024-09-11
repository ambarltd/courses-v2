<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Aggregate;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

/**
 * Describes a contact between two @see User.
 * It covers every possible state, whether the contact is pending, active, or inactive.
 *
 * Although each pair of users should only have one Contact aggregate root,
 * it is not possible to enforce this in practice, because each aggregate root lives in isolation.
 * Therefore be prepared to think about this when implementing write side validation,
 * and when implementing the read model.
 *
 * Each event should include which user is performing the action, in case tracking
 * who did what is needed later.
 */
class Contact implements Aggregate
{
    use AggregateTrait;

    /**
     * @var PendingContactRequest|ActiveContact|InactiveContact
     */
    private $contactStatus;

    /**
     * @return PendingContactRequest|ActiveContact|InactiveContact
     */
    public function contactStatus()
    {
        return $this->contactStatus;
    }

    /**
     * @param PendingContactRequest|ActiveContact|InactiveContact $contactStatus
     */
    public static function fromStatus(
        Id $id,
        $contactStatus
    ): Contact {
        $contact = new self($id);

        $contact->contactStatus = $contactStatus;

        return $contact;
    }
}
