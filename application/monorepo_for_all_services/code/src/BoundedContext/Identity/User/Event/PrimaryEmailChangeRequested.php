<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedButRequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Event\EventTrait;
use Galeas\Api\Common\Id\Id;

class PrimaryEmailChangeRequested implements EventTransformedUser
{
    use EventTrait;

    private string $newEmailRequested;

    private string $newVerificationCode;

    private string $requestedWithHashedPassword;

    public function newEmailRequested(): string
    {
        return $this->newEmailRequested;
    }

    public function newVerificationCode(): string
    {
        return $this->newVerificationCode;
    }

    public function requestedWithHashedPassword(): string
    {
        return $this->requestedWithHashedPassword;
    }

    public static function new(
        Id $eventId,
        Id $aggregateId,
        int $aggregateVersion,
        Id $causationId,
        Id $correlationId,
        \DateTimeImmutable $recordedOn,
        array $metadata,
        string $newEmailRequested,
        string $newVerificationCode,
        string $requestedWithHashedPassword
    ): PrimaryEmailChangeRequested {
        $event = new self(
            $eventId,
            $aggregateId,
            $aggregateVersion,
            $causationId,
            $correlationId,
            $recordedOn,
            $metadata
        );

        $event->newEmailRequested = $newEmailRequested;
        $event->newVerificationCode = $newVerificationCode;
        $event->requestedWithHashedPassword = $requestedWithHashedPassword;

        return $event;
    }

    public function transformUser(User $user): User
    {
        $previousEmailStatus = $user->primaryEmailStatus();

        if ($previousEmailStatus instanceof UnverifiedEmail) {
            return User::fromProperties(
                $user->aggregateId(),
                $this->aggregateVersion,
                UnverifiedEmail::fromEmailAndVerificationCode(
                    Email::fromEmail(
                        $this->newEmailRequested
                    ),
                    VerificationCode::fromVerificationCode(
                        $this->newVerificationCode
                    )
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            );
        } elseif ($previousEmailStatus instanceof VerifiedEmail) {
            return User::fromProperties(
                $user->aggregateId(),
                $this->aggregateVersion,
                VerifiedButRequestedNewEmail::fromEmailsAndVerificationCode(
                    Email::fromEmail(
                        $previousEmailStatus->email()->email()
                    ),
                    Email::fromEmail(
                        $this->newEmailRequested
                    ),
                    VerificationCode::fromVerificationCode(
                        $this->newVerificationCode
                    )
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            );
        } else {
            return User::fromProperties(
                $user->aggregateId(),
                $this->aggregateVersion,
                VerifiedButRequestedNewEmail::fromEmailsAndVerificationCode(
                    Email::fromEmail(
                        $previousEmailStatus->verifiedEmail()->email()
                    ),
                    Email::fromEmail(
                        $this->newEmailRequested
                    ),
                    VerificationCode::fromVerificationCode(
                        $this->newVerificationCode
                    )
                ),
                $user->hashedPassword(),
                $user->accountDetails()
            );
        }
    }
}
