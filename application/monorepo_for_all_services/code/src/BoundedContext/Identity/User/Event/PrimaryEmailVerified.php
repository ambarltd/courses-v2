<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class PrimaryEmailVerified implements EventTransformedUser
{
    use EventTrait;

    private string $verifiedWithCode;

    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        string $verifiedWithCode
    ): PrimaryEmailVerified {
        $event = new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );
        $event->verifiedWithCode = $verifiedWithCode;

        return $event;
    }

    public function verifiedWithCode(): string
    {
        return $this->verifiedWithCode;
    }

    public function transformUser(User $user): User
    {
        if ($user->primaryEmailStatus() instanceof UnverifiedEmail) {
            return User::fromProperties(
                $user->aggregateId(),
                $this->aggregateVersion,
                VerifiedEmail::fromEmail(
                    Email::fromEmail(
                        $user->primaryEmailStatus()
                            ->email()
                            ->email()
                    )
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            );
        } elseif ($user->primaryEmailStatus() instanceof VerifiedEmail) {
            return User::fromProperties(
                $user->aggregateId(),
                $this->aggregateVersion,
                VerifiedEmail::fromEmail(
                    Email::fromEmail(
                        $user->primaryEmailStatus()
                            ->email()
                            ->email()
                    )
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            );
        } else {
            return User::fromProperties(
                $user->aggregateId(),
                $this->aggregateVersion,
                VerifiedEmail::fromEmail(
                    Email::fromEmail(
                        $user->primaryEmailStatus()
                            ->requestedEmail()
                            ->email()
                    )
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            );
        }
    }
}
