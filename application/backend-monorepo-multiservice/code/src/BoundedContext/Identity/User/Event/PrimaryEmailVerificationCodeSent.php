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

    private string $toEmailAddress;

    private string $emailContents;

    private string $fromEmailAddress;

    private string $subjectLine;

    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        string $verificationCodeSent,
        string $toEmailAddress,
        string $emailContents,
        string $fromEmailAddress,
        string $subjectLine
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
        $event->toEmailAddress = $toEmailAddress;
        $event->emailContents = $emailContents;
        $event->fromEmailAddress = $fromEmailAddress;
        $event->subjectLine = $subjectLine;

        return $event;
    }

    public function verificationCodeSent(): string
    {
        return $this->verificationCodeSent;
    }

    public function toEmailAddress(): string
    {
        return $this->toEmailAddress;
    }

    public function emailContents(): string
    {
        return $this->emailContents;
    }

    public function fromEmailAddress(): string
    {
        return $this->fromEmailAddress;
    }

    public function subjectLine(): string
    {
        return $this->subjectLine;
    }

    public function transformUser(User $user): User
    {
        return $user;
    }
}
