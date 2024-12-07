<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Identity\User\Projection\UserDetails;

class UserDetails
{
    private string $id;

    private string $username;

    private ?string $verifiedEmail;

    private ?string $unverifiedEmail;

    private function __construct() {}

    public function userId(): string
    {
        return $this->id;
    }

    public function username(): string
    {
        return $this->username;
    }

    public function verifiedEmail(): ?string
    {
        return $this->verifiedEmail;
    }

    public function unverifiedEmail(): ?string
    {
        return $this->unverifiedEmail;
    }

    public function verifyEmail(): self
    {
        $this->verifiedEmail = $this->unverifiedEmail;
        $this->unverifiedEmail = null;

        return $this;
    }

    public function requestNewEmail(string $newEmailRequested): self
    {
        $this->unverifiedEmail = $newEmailRequested;

        return $this;
    }

    public static function fromUserIdUsernameAndEmails(
        string $userId,
        string $username,
        ?string $verifiedEmail,
        ?string $requestedEmail
    ): self {
        $userDetails = new self();
        $userDetails->id = $userId;
        $userDetails->username = $username;
        $userDetails->verifiedEmail = null;
        $userDetails->unverifiedEmail = null;

        if (\is_string($verifiedEmail)) {
            $userDetails->verifiedEmail = $verifiedEmail;
        }
        if (\is_string($requestedEmail)) {
            $userDetails->unverifiedEmail = $requestedEmail;
        }

        return $userDetails;
    }
}
