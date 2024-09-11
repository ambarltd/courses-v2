<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Messaging\Contact\Event;

use Galeas\Api\BoundedContext\Messaging\Contact\Aggregate\Contact;
use Galeas\Api\BoundedContext\Messaging\Contact\ValueObject\PendingContactRequest;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class ContactRequested implements EventCreatedContact
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
        Id $authorizerId,
        array $metadata,
        Id $requesterContact,
        Id $requestedContact
    ): ContactRequested {
        $aggregateId = Id::createNew();
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->requesterContact = $requesterContact;
        $event->requestedContact = $requestedContact;

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function createContact(): Contact
    {
        return Contact::fromStatus(
            $this->aggregateId(),
            PendingContactRequest::fromContacts(
                $this->requesterContact,
                $this->requestedContact
            )
        );
    }
}
