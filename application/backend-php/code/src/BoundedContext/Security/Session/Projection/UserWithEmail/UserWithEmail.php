<?php

declare(strict_types=1);

namespace Galeas\Api\BoundedContext\Security\Session\Projection\UserWithEmail;

class UserWithEmail
{
    private string $id;

    private ?string $lowercaseVerifiedEmail;

    private ?string $lowercaseRequestedEmail;

    private function __construct() {}

    public function userId(): string
    {
        return $this->id;
    }

    public function verifyEmail(): self
    {
        $this->lowercaseVerifiedEmail = $this->lowercaseRequestedEmail;
        $this->lowercaseRequestedEmail = null;

        return $this;
    }

    public function requestNewEmail(string $newEmailRequested): self
    {
        $this->lowercaseRequestedEmail = strtolower($newEmailRequested);

        return $this;
    }

    public static function fromUserIdAndEmails(
        string $userId,
        ?string $verifiedEmail,
        ?string $requestedEmail
    ): self {
        $takenEmail = new self();
        $takenEmail->id = $userId;
        $takenEmail->lowercaseVerifiedEmail = null;
        $takenEmail->lowercaseRequestedEmail = null;

        if (\is_string($verifiedEmail)) {
            $takenEmail->lowercaseVerifiedEmail = strtolower($verifiedEmail);
        }
        if (\is_string($requestedEmail)) {
            $takenEmail->lowercaseRequestedEmail = strtolower($requestedEmail);
        }

        return $takenEmail;
    }
}
