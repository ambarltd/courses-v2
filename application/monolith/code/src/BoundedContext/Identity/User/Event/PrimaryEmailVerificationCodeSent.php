<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class PrimaryEmailVerificationCodeSent implements EventTransformedUser
{
    use EventTrait;

    /**
     * @var string
     */
    private $verifiedWithCode;

    /**
     * @var string
     */
    private $sentToEmailAddress;

    /**
     * @var string
     */
    private $emailContents;

    public static function fromProperties(
        Id $eventId,
        Id $aggregateId,
        Id $causationId,
        array $metadata,
        string $sentToEmailAddress,
        string $emailContents
    ): PrimaryEmailVerificationCodeSent {
        $event = new self($eventId, $aggregateId, $causationId, $metadata);

        $event->sentToEmailAddress = $sentToEmailAddress;
        $event->emailContents = $emailContents;

        return $event;
    }

    public function sentToEmailAddress(): string
    {
        return $this->sentToEmailAddress;
    }

    public function emailContents(): string
    {
        return $this->emailContents;
    }

    /**
     * {@inheritdoc}
     */
    public function transformUser(User $user): User
    {
        return $user;
    }
}
