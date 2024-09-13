<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\ValueObject;

class SessionIsSignedOut
{
    /**
     * @var string
     */
    private $withSessionToken;

    /**
     * @var string
     */
    private $withIp;

    private function __construct()
    {
    }

    public function withSessionToken(): string
    {
        return $this->withSessionToken;
    }

    public function withIp(): string
    {
        return $this->withIp;
    }

    public static function fromProperties(
        string $withSessionToken,
        string $withIp
    ): self {
        $sessionIsSignedOut = new self();

        $sessionIsSignedOut->withSessionToken = $withSessionToken;
        $sessionIsSignedOut->withIp = $withIp;

        return $sessionIsSignedOut;
    }
}
