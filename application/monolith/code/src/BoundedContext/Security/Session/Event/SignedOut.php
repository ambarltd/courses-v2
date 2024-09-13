<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Event;

use Galeas\Api\BoundedContext\Security\Session\Aggregate\Session;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use Galeas\Api\Common\Event\EventWithAuthorizerAndNoSourceTrait;
use Galeas\Api\Common\Id\Id;

class SignedOut implements EventTransformedSession
{
    use EventWithAuthorizerAndNoSourceTrait;

    /**
     * @var string
     */
    private $withIp;

    /**
     * @var string
     */
    private $withSessionToken;

    public function withIp(): string
    {
        return $this->withIp;
    }

    public function withSessionToken(): string
    {
        return $this->withSessionToken;
    }

    /**
     * @return SignedOut
     */
    public static function fromProperties(
        Id $aggregateId,
        Id $authorizerId,
        array $metadata,
        string $withIp,
        string $withSessionToken
    ): self {
        $event = new self($aggregateId, $authorizerId, $metadata);

        $event->withIp = $withIp;
        $event->withSessionToken = $withSessionToken;

        return $event;
    }

    /**
     * {@inheritdoc}
     */
    public function transformSession(Session $session): Session
    {
        return Session::fromProperties(
            $session->id(),
            $session->sessionDetails(),
            SessionIsSignedOut::fromProperties(
                $this->withSessionToken,
                $this->withIp
            )
        );
    }
}
