<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;
use Galeas\Api\Primitive\PrimitiveCreation\SessionToken\SessionTokenCreator;

class TokenRefreshed implements EventTransformedSession
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var string
     */
    private $withIp;

    /**
     * @var string
     */
    private $withExistingSessionToken;

    /**
     * @var string
     */
    private $refreshedSessionToken;

    public function withIp(): string
    {
        return $this->withIp;
    }

    public function withExistingSessionToken(): string
    {
        return $this->withExistingSessionToken;
    }

    public function refreshedSessionToken(): string
    {
        return $this->refreshedSessionToken;
    }

    /**
     * @return TokenRefreshed
     */
    public static function fromProperties(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        string $withIp,
        string $withExistingSessionToken
    ): self {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->withIp = $withIp;
        $event->withExistingSessionToken = $withExistingSessionToken;
        $event->refreshedSessionToken = SessionTokenCreator::create();

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function transformSession(Session $session): Session
    {
        return Session::fromProperties(
            $session->id(),
            SessionDetails::fromProperties(
                $session->sessionDetails()->asUser(),
                $session->sessionDetails()->withUsername(),
                $session->sessionDetails()->withEmail(),
                $session->sessionDetails()->withHashedPassword(),
                $session->sessionDetails()->byDeviceLabel(),
                $this->withIp,
                $this->refreshedSessionToken
            ),
            $session->sessionIsSignedOut()
        );
    }
}
