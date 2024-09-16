<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class PrimaryEmailVerificationCodeSent implements EventTransformedUser
{
    use EventTrait;

    private string $verificationCodeSent;

    private string $sentToEmailAddress;

    private string $emailContents;

    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        string $verificationCodeSent,
        string $sentToEmailAddress,
        string $emailContents
    ): PrimaryEmailVerificationCodeSent {
        $event = new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );
        $event->verificationCodeSent = $verificationCodeSent;
        $event->sentToEmailAddress = $sentToEmailAddress;
        $event->emailContents = $emailContents;

        return $event;
    }

    public function verificationCodeSent(): string
    {
        return $this->verificationCodeSent;
    }

    public function sentToEmailAddress(): string
    {
        return $this->sentToEmailAddress;
    }

    public function emailContents(): string
    {
        return $this->emailContents;
    }

    public function transformUser(User $user): User
    {
        return $user;
    }
}
