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
 * The benefit is that we make explicit who the rejecter, and who the rejected is.
 * Another benefit is that projections become easier.
 */
class ContactRequestRejected implements EventTransformedContact
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $rejecterContact;

    /**
     * @var Id
     */
    private $rejectedContact;

    public function rejecterContact(): Id
    {
        return $this->rejecterContact;
    }

    public function rejectedContact(): Id
    {
        return $this->rejectedContact;
    }

    public static function fromContacts(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        Id $rejecterContact,
        Id $rejectedContact
    ): ContactRequestRejected {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->rejecterContact = $rejecterContact;
        $event->rejectedContact = $rejectedContact;

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
                $this->rejecterContact,
                $this->rejectedContact
            )
        );
    }
}
