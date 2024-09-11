<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\ActiveContact;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

/**
 * De-normalized contacts in this event. Trade-off understood.
 * The contact ids might conflict with previous contacts, so logic needs to be there to address that.
 * The benefit is that we make explicit who the accepter, and who the accepted is.
 * Another benefit is that projections become easier.
 */
class ContactRequestAccepted implements EventTransformedContact
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $accepterContact;

    /**
     * @var Id
     */
    private $acceptedContact;

    public function accepterContact(): Id
    {
        return $this->accepterContact;
    }

    public function acceptedContact(): Id
    {
        return $this->acceptedContact;
    }

    public static function fromContacts(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        Id $accepterContact,
        Id $acceptedContact
    ): ContactRequestAccepted {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->accepterContact = $accepterContact;
        $event->acceptedContact = $acceptedContact;

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function transformContact(Contact $contact): Contact
    {
        return Contact::fromStatus(
            $contact->id(),
            ActiveContact::fromContacts(
                $this->accepterContact,
                $this->acceptedContact
            )
        );
    }
}
