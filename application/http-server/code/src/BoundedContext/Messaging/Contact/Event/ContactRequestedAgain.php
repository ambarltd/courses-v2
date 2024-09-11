<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

/**
 * De-normalized contacts in this event. Trade-off understood.
 * The contact ids might conflict with previous contacts, so logic needs to be there to address that.
 * The benefit is that we make explicit who the requester, and who the requested is.
 * Another benefit is that projections become easier.
 */
class ContactRequestedAgain implements EventTransformedContact
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $requesterContact;

    /**
     * @var Id
     */
    private $requestedContact;

    public function requesterContact(): Id
    {
        return $this->requesterContact;
    }

    public function requestedContact(): Id
    {
        return $this->requestedContact;
    }

    public static function fromContacts(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        Id $requesterContact,
        Id $requestedContact
    ): ContactRequestedAgain {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->requesterContact = $requesterContact;
        $event->requestedContact = $requestedContact;

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function transformContact(Contact $contact): Contact
    {
        return Contact::fromStatus(
            $contact->id(),
            PendingContactRequest::fromContacts(
                $this->requesterContact,
                $this->requestedContact
            )
        );
    }
}
