<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetailsV2\ValueObject;

class VerifiedEmailButRequestedNewEmail
{
    private string $verifiedEmail;
    private string $requestedEmail;

    private function __construct() {}

    public function getVerifiedEmail(): string
    {
        return $this->verifiedEmail;
    }

    public function getRequestedEmail(): string
    {
        return $this->requestedEmail;
    }

    public static function fromProperties(string $verifiedEmail, string $requestedEmail): self
    {
        $instance = new self();
        $instance->verifiedEmail = $verifiedEmail;
        $instance->requestedEmail = $requestedEmail;

        return $instance;
    }
}
