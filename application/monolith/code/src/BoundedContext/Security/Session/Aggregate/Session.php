<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Aggregate;

use Galeas\Api\BoundedContext\Identity\User\Aggregate\User;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionDetails;
use Galeas\Api\BoundedContext\Security\Session\ValueObject\SessionIsSignedOut;
use Galeas\Api\Common\Aggregate\Aggregate;
use Galeas\Api\Common\Aggregate\AggregateTrait;
use Galeas\Api\Common\Id\Id;

/**
 * Aggregate for a session by a @see User.
 */
class Session implements Aggregate
{
    use AggregateTrait;

    /**
     * @var SessionDetails
     */
    private $sessionDetails;

    /**
     * @var SessionIsSignedOut|null
     */
    private $sessionIsSignedOut;

    public function sessionDetails(): SessionDetails
    {
        return $this->sessionDetails;
    }

    public function sessionIsSignedOut(): ?SessionIsSignedOut
    {
        return $this->sessionIsSignedOut;
    }

    public static function fromProperties(
        Id $id,
        SessionDetails $sessionDetails,
        ?SessionIsSignedOut $sessionIsSignedOut
    ): self {
        $session = new self($id);

        $session->sessionDetails = $sessionDetails;
        $session->sessionIsSignedOut = $sessionIsSignedOut;

        return $session;
    }
}
