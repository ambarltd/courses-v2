<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Event;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\Email;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\RequestedNewEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\UnverifiedEmail;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerificationCode;
use Galeas\Api\BoundedContext\Identity\User\ValueObject\VerifiedEmail;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveCreation\Email\EmailVerificationCodeCreator;

class PrimaryEmailChangeRequested implements EventTransformedUser
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var string
     */
    private $newEmailRequested;

    /**
     * @var string
     */
    private $newVerificationCode;

    /**
     * @var string
     */
    private $requestedWithHashedPassword;

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

    public static function fromProperties(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        string $newEmailRequested,
        string $requestedWithHashedPassword
    ): PrimaryEmailChangeRequested {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->newEmailRequested = $newEmailRequested;
        $event->newVerificationCode = EmailVerificationCodeCreator::create();
        $event->requestedWithHashedPassword = $requestedWithHashedPassword;

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function transformUser(User $user): User
    {
        $previousEmailStatus = $user->primaryEmailStatus();

        if ($previousEmailStatus instanceof UnverifiedEmail) {
            return User::fromProperties(
                $user->id(),
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
                $user->id(),
                RequestedNewEmail::fromEmailsAndVerificationCode(
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
                $user->id(),
                RequestedNewEmail::fromEmailsAndVerificationCode(
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
