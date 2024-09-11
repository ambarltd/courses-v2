<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\InactiveContact;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

/**
 * De-normalized contacts in this event. Trade-off understood.
 * The contact ids might conflict with previous contacts, so logic needs to be there to address that.
 * The benefit is that we make explicit who the canceller, and who the cancelled is.
 * Another benefit is that projections become easier.
 */
class ContactRequestCancelled implements EventTransformedContact
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $cancellerContact;

    /**
     * @var Id
     */
    private $cancelledContact;

    public function cancellerContact(): Id
    {
        return $this->cancellerContact;
    }

    public function cancelledContact(): Id
    {
        return $this->cancelledContact;
    }

    public static function fromContacts(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        Id $cancellerContact,
        Id $cancelledContact
    ): ContactRequestCancelled {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->cancellerContact = $cancellerContact;
        $event->cancelledContact = $cancelledContact;

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function transformContact(Contact $contact): Contact
    {
        return Contact::fromStatus(
            $contact->id(),
            InactiveContact::fromContacts(
                $this->cancellerContact,
                $this->cancelledContact
            )
        );
    }
}
