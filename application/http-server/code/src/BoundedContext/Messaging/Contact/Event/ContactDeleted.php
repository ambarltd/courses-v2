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
 * The benefit is that we make explicit who the deleter, and who the deleted is.
 * Another benefit is that projections become easier.
 */
class ContactDeleted implements EventTransformedContact
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var Id
     */
    private $deleterContact;

    /**
     * @var Id
     */
    private $deletedContact;

    public function deleterContact(): Id
    {
        return $this->deleterContact;
    }

    public function deletedContact(): Id
    {
        return $this->deletedContact;
    }

    public static function fromContacts(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        Id $deleterContact,
        Id $deletedContact
    ): ContactDeleted {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->deleterContact = $deleterContact;
        $event->deletedContact = $deletedContact;

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
                $this->deleterContact,
                $this->deletedContact
            )
        );
    }
}
