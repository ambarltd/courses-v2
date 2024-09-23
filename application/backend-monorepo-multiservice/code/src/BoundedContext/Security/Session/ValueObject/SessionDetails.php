<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\ValueObject;

use Galeas\Api\Common\Id\Id;

class SessionDetails
{
    private Id $asUser;

    private ?string $withUsername;

    private ?string $withEmail;

    private string $withHashedPassword;

    private string $byDeviceLabel;

    private string $withIp;

    private string $withSessionToken;

    private function __construct() {}

    public function asUser(): Id
    {
        return $this->asUser;
    }

    public function withUsername(): ?string
    {
        return $this->withUsername;
    }

    public function withEmail(): ?string
    {
        return $this->withEmail;
    }

    public function withHashedPassword(): string
    {
        return $this->withHashedPassword;
    }

    public function byDeviceLabel(): string
    {
        return $this->byDeviceLabel;
    }

    public function withIp(): string
    {
        return $this->withIp;
    }

    public function withSessionToken(): string
    {
        return $this->withSessionToken;
    }

    public static function fromProperties(
        Id $asUser,
        ?string $withUsername,
        ?string $withEmail,
        string $withHashedPassword,
        string $byDeviceLabel,
        string $withIp,
        string $withSessionToken
    ): self {
        $session = new self();

        $session->asUser = $asUser;
        $session->withUsername = $withUsername;
        $session->withEmail = $withEmail;
        $session->withHashedPassword = $withHashedPassword;
        $session->byDeviceLabel = $byDeviceLabel;
        $session->withIp = $withIp;
        $session->withSessionToken = $withSessionToken;

        return $session;
    }
}
